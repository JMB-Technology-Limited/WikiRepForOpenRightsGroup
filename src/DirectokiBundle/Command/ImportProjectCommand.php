<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Entity\Directory;
use DirectokiBundle\Entity\Field;
use DirectokiBundle\Entity\Locale;
use DirectokiBundle\Entity\Project;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use DirectokiBundle\Exception\DataValidationException;
use DirectokiBundle\FieldType\FieldTypeEmail;
use DirectokiBundle\FieldType\FieldTypeLatLng;
use DirectokiBundle\FieldType\FieldTypeMultiSelect;
use DirectokiBundle\FieldType\FieldTypeString;
use DirectokiBundle\FieldType\FieldTypeText;
use DirectokiBundle\FieldType\FieldTypeURL;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportProjectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:import-project')
            ->setHelp('This command allows you toimport a project.')
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

        $output->writeln('Import Project '.($save ? '(SAVE)' : '(test run)'));

        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        $config = parse_ini_file($input->getArgument('config'), true);

        ########################################################## User
        $user = $doctrine->getRepository('JMBTechnologyUserAccountsBundle:User')->findOneByEmail($config['user']['email']);
        if (!$user) {
            $output->writeln('Cant load User');
            return;
        }
        $output->writeln('User: '. $user->getEmail());

        ########################################################## Project

        $project = new Project();
        $project->setTitle($config['project']['title']);
        $project->setPublicId($config['project']['public_id']);
        $project->setOwner($user);

        $output->writeln('-- Project');
        $output->writeln('ID: '. $project->getPublicId());
        $output->writeln('Title: '. $project->getTitle());

        if ($save) {
            $doctrine->persist($project);
        }

        ########################################################## Event
        $event = $this->getContainer()->get('directoki_event_builder_service')->build(
            $project,
            null,
            null,
            isset($config['general']['comment']) ? $config['general']['comment'] : ''
        );
        if ($save) {
            $doctrine->persist($event);
        }

        ########################################################## Directory

        $directory = new Directory();
        $directory->setProject($project);
        $directory->setPublicId($config['directory']['public_id']);
        $directory->setTitlePlural($config['directory']['title_plural']);
        $directory->setTitleSingular($config['directory']['title_singular']);
        $directory->setCreationEvent($event);

        $output->writeln('-- Directory');
        $output->writeln('ID: '. $directory->getPublicId());
        $output->writeln('Title Singular: '. $directory->getTitleSingular());
        $output->writeln('Title Plural: '. $directory->getTitlePlural());

        if ($save) {
            $doctrine->persist($directory);
        }

        ########################################################## Locales

        foreach($config as $header=>$section) {
            if (substr($header, 0, 7) == 'locale_') {
                $locale = new Locale();
                $locale->setProject($project);
                $locale->setCreationEvent($event);
                $locale->setPublicId($section['public_id']);
                $locale->setTitle($section['title']);

                $output->writeln('-- Locale');
                $output->writeln('ID: '. $locale->getPublicId());
                $output->writeln('Title Singular: '. $locale->getTitle());

                if ($save) {
                    $doctrine->persist($locale);
                }

            }
        }

        ########################################################## Fields

        $sort = 0;
        foreach($config as $header=>$section) {
            if (substr($header, 0, 6) == 'field_') {

                $field = new Field();
                $field->setSort($sort);
                $field->setCreationEvent($event);
                $field->setDirectory($directory);
                $field->setTitle($section['title']);
                $field->setPublicId($section['public_id']);

                $type = trim(strtolower($section['type']));
                if ($type == 'string') {
                    $field->setFieldType(FieldTypeString::FIELD_TYPE_INTERNAL);
                } else if ($type == 'text') {
                    $field->setFieldType(FieldTypeText::FIELD_TYPE_INTERNAL);
                } else if ($type == 'email') {
                    $field->setFieldType(FieldTypeEmail::FIELD_TYPE_INTERNAL);
                } else if ($type == 'url') {
                    $field->setFieldType(FieldTypeURL::FIELD_TYPE_INTERNAL);
                } else if ($type == 'multiselect') {
                    $field->setFieldType(FieldTypeMultiSelect::FIELD_TYPE_INTERNAL);
                } else if ($type == 'latlng') {
                    $field->setFieldType(FieldTypeLatLng::FIELD_TYPE_INTERNAL);
                } else {
                    $output->writeln('Field Type Not Known: '.$type);
                    return;
                }

                $output->writeln('-- Field');
                $output->writeln('ID: '. $field->getPublicId());
                $output->writeln('Title: '. $field->getTitle());
                $output->writeln('Type:' .$field->getFieldType());

                if ($save) {
                    $doctrine->persist($field);
                }

                $sort++;

            }
        }


        ########################################################## Write

        if ($save) {
            $doctrine->flush();
        }

        $output->writeln('Done!');

    }
}

