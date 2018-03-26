<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Action\ChangeFieldTypeStringToText;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ExportDirectoryAsSpreadsheetCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:export-directory-as-spreadsheet')
            ->setDescription('Export Directory As Spreadsheet.')
            ->setHelp('This command allows you to Export a Directory As Spreadsheet')
            ->addArgument('project', InputArgument::REQUIRED, 'Project')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory')
            ->addArgument('file', InputArgument::REQUIRED, 'Output File');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($input->getArgument('project'));
        if (!$project) {
            $output->writeln('Cant load Project');
            return;
        }
        $output->writeln('Project: '. $project->getTitle());

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('publicId'=>$input->getArgument('directory'),'project'=>$project));
        if (!$directory) {
            $output->writeln('Cant load Directory');
            return;
        }
        $output->writeln('Directory: '. $directory->getTitleSingular());

        $file =   $input->getArgument('file');
        $output->writeln('File: '. $file);

        $fp = fopen($file, 'w');

        $records = $doctrine->getRepository('DirectokiBundle:Record')->findBy(array('directory'=>$directory, 'cachedState'=>RecordHasState::STATE_PUBLISHED));

        $fields = $doctrine->getRepository('DirectokiBundle:Field')->findForDirectory($directory);

        $out = array(
            'id'
        );

        foreach($fields as $field) {
            $fieldType = $this->getContainer()->get('directoki_field_type_service')->getByField($field);
            $out = array_merge($out, $fieldType->getExportCSVHeaders($field));
        }

        fputcsv($fp, $out);

        foreach($records as $record) {

            $out = array(
                $record->getPublicId(),
            );

            foreach($fields as $field) {
                $fieldType = $this->getContainer()->get('directoki_field_type_service')->getByField($field);
                $out = array_merge($out, $fieldType->getExportCSVData($field, $record));
            }

            fputcsv($fp, $out);

        }

        fclose($fp);




        $output->writeln('Done.');

    }

}
