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
class ChangeFieldTypeStringToTextCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:change-field-type-string-to-text')
            ->setDescription('Change Field Type - String to Text.')
            ->setHelp('This command allows you toChange Field Type - String to Text.')
            ->addArgument('project', InputArgument::REQUIRED, 'Project')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory')
            ->addArgument('field', InputArgument::REQUIRED, 'Field');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->getContainer()->getParameter('directoki.read_only')) {
            $output->writeln('Directoki is currently read only.');
            return;
        }


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

        $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneByPublicId(array('publicId'=>$input->getArgument('field'),'directory'=>$directory));
        if (!$field) {
            $output->writeln('Cant load Field');
            return;
        }
        $output->writeln('Field: '. $field->getTitle());

        // We also allow Text because a change might have stopped half way through and we want people to be able to run it again.
        if ($field->getFieldType() != FieldTypeString::FIELD_TYPE_INTERNAL && $field->getFieldType() != FieldTypeText::FIELD_TYPE_INTERNAL) {
            $output->writeln('Field is not a Text Or String!');
            return;
        }

        $action = new ChangeFieldTypeStringToText($this->getContainer());
        $action->change($field);

        $output->writeln('Done.');

    }

}
