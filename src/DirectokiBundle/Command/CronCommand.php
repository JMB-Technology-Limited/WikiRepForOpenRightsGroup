<?php

namespace DirectokiBundle\Command;


use DirectokiBundle\Cron\DeleteOldInformation;
use DirectokiBundle\Cron\ExternalCheck;
use DirectokiBundle\Cron\UpdateFieldCache;
use DirectokiBundle\Cron\UpdateRecordCache;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 *
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class CronCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:cron')
            ->setDescription('Run Tasks')
            ->setHelp('This command runs tasks.');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->getContainer()->getParameter('directoki.read_only')) {
            $output->writeln('Directoki is currently read only.');
            return;
        }

        $cronCommands = array(
            new ExternalCheck($this->getContainer()),
            new UpdateRecordCache($this->getContainer()),
            new UpdateFieldCache($this->getContainer()),
            new DeleteOldInformation($this->getContainer()),
        );

        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        foreach($doctrine->getRepository('DirectokiBundle:Project')->findAll() as $project) {
            $output->writeln('Project: '. $project->getTitle());
            foreach($doctrine->getRepository('DirectokiBundle:Directory')->findBy(array('project'=>$project)) as $directory) {
                $output->writeln('Directory: '. $directory->getTitleSingular());
                foreach($doctrine->getRepository('DirectokiBundle:Field')->findBy(array('directory'=>$directory)) as $field) {
                    $output->writeln('Field: '. $field->getPublicId());
                    foreach($cronCommands as $cronCommand) {
                        $cronCommand->runForField($field);
                    }
                }
                foreach($doctrine->getRepository('DirectokiBundle:Record')->findBy(array('directory'=>$directory)) as $record) {
                    $output->writeln('Record: '. $record->getPublicId());

                    foreach($cronCommands as $cronCommand) {
                        $cronCommand->runForRecord($record);
                    }

                }
            }
        }

        $output->writeln('Misc:');
        foreach($cronCommands as $cronCommand) {
            $cronCommand->run($record);
        }

        $output->writeln('Done!');

    }



}
