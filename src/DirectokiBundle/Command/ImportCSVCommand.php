<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Action\UpdateSelectValueCache;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Entity\SelectValue;
use DirectokiBundle\Exception\DataValidationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportCSVCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:import-csv')
            ->setDescription('Import a CSV.')
            ->setHelp('This command allows you to import a CSV.')
            ->addArgument('config', InputArgument::REQUIRED, 'Config')
            ->addOption('save',null,InputOption::VALUE_NONE,'Actually Save Changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->getContainer()->getParameter('directoki.read_only')) {
            $output->writeln('Directoki is currently read only.');
            return;
        }

        $save = $input->getOption('save');

        $output->writeln('Import CSV '.($save ? '(SAVE)' : '(test run)'));

        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        $config = parse_ini_file($input->getArgument('config'), true);
        $publish = isset($config['general']['publish']) ? boolval($config['general']['publish']) : false;

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($config['general']['project']);
        if (!$project) {
            $output->writeln('Cant load Project');
            return;
        }
        $output->writeln('Project: '. $project->getTitle());

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$config['general']['directory']));
        if (!$directory) {
            $output->writeln('Cant load Directory');
            return;
        }
        $output->writeln('Directory: '. $directory->getTitleSingular());

        $fileName = null;
        if (isset($config['general']['url']) && filter_var($config['general']['url'], FILTER_VALIDATE_URL)) {
            $fileName = tempnam(sys_get_temp_dir(), 'DirectokiImport');
            $output->writeln('Downloading File ...');
            file_put_contents($fileName, fopen($config['general']['url'], 'r'));
        } else if (file_exists($config['general']['file']) && is_readable($config['general']['file'])) {
            $output->writeln('Found File');
            $fileName = $config['general']['file'];
        } else {
            $output->writeln('Cant load File');
            return;
        }

        $event = $this->getContainer()->get('directoki_event_builder_service')->build(
            $project,
            null,
            null,
            isset($config['general']['comment']) ? $config['general']['comment'] : ''
        );
        if ($save) {
            $doctrine->persist($event);
        }

        $fields = array();
        foreach($config as $header=>$section) {
            if (substr($header,0,6) == 'field_') {
                $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$directory, 'publicId'=>substr($header, 6)));
                if (!$field) {
                    $output->writeln('Cant load Field');
                    return;
                }
                $fields[substr($header, 6)] = array(
                    'field'=>$field,
                    'fieldType'=>$this->getContainer()->get( 'directoki_field_type_service' )->getByField( $field ),
                    'config'=>$section
                );
            }
        }

        $csvLength = isset($config['csv']) && isset($config['csv']['length']) ? $config['csv']['length'] : 0;
        $csvDelimiter = isset($config['csv']) && isset($config['csv']['delimiter']) ? $config['csv']['delimiter'] : ',';
        $csvEnclosure = isset($config['csv']) && isset($config['csv']['enclosure']) ? $config['csv']['enclosure'] : '"';
        $csvEscape = isset($config['csv']) && isset($config['csv']['escape']) ? $config['csv']['escape'] : "\\";

        $updateCacheAction = new UpdateRecordCache($this->getContainer());
        $updateSelectValueCacheAction = new UpdateSelectValueCache($this->getContainer());
        $file = fopen($fileName, 'r');


        $lineNumber = 1;
        if ($config['csv']['skip_first_line'] && $config['csv']['skip_first_line']) {
            $line = fgetcsv($file, $csvLength, $csvDelimiter, $csvEnclosure, $csvEscape);
            $lineNumber++;
        }
        while($line = fgetcsv($file, $csvLength, $csvDelimiter, $csvEnclosure, $csvEscape)) {

            $output->writeln('Line '.$lineNumber.'...');
            $lineNumber++;

            $record = new Record();
            $record->setCreationEvent( $event );
            $record->setDirectory($directory);
            $record->setCachedState($publish ? RecordHasState::STATE_PUBLISHED : RecordHasState::STATE_DRAFT);

            if ($save) {
                $doctrine->persist($record);

                if ($publish) {
                    // import published
                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord( $record );
                    $recordHasState->setCreationEvent( $event );
                    $recordHasState->setApprovalEvent($event);
                    $recordHasState->setState( RecordHasState::STATE_PUBLISHED );
                    $doctrine->persist( $recordHasState );
                } else {
                    // Also record a request to publish this record but don't approve it - moderator will do that.
                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord( $record );
                    $recordHasState->setCreationEvent( $event );
                    $recordHasState->setState( RecordHasState::STATE_PUBLISHED );
                    $doctrine->persist( $recordHasState );
                }
            }

            $selectValues = [];
            foreach($fields as $fieldName=>$fieldData) {
                try {
                    $return = $fieldData['fieldType']->parseCSVLineData($fieldData['field'], $fieldData['config'], $line, $record, $event, $publish);
                    if ($return) {

                        $output->writeln(' ... ' . $fieldName . ' : ' . $return->getDebugOutput());

                        if ($save) {
                            foreach ($return->getEntitiesToSave() as $entityToSave) {
                                $doctrine->persist($entityToSave);
                                if ($entityToSave instanceof SelectValue) {
                                    $selectValues[] = $entityToSave;
                                }
                            }
                        }

                    }
                } catch (DataValidationException $dataValidationException) {

                    $output->writeln(' ... ' . $fieldName . ' ERROR : ' . $dataValidationException->getMessage());
                    $output->writeln('ERROR! EXITING NOW!');
                    return;
                }
            }

            if ($save) {
                $doctrine->flush();
                $output->writeln(' ... ... Saved as: '.$record->getPublicId());
                foreach($selectValues as $selectValue) {
                    $updateSelectValueCacheAction->go($selectValue);
                }
                $updateCacheAction->go($record);
                $output->writeln(' ... ... ... and cache updated!');
            }

        }
        fclose($file);

        $output->writeln('Done!');

    }
}

