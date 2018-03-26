<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Action\UpdateRecordCache;
use DirectokiBundle\Cron\UpdateFieldCache;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class UpdateAllCachesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:update-all-caches')
            ->setDescription('Updates all Caches.')
            ->setHelp('This command allows you to Updates all Caches');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if ($this->getContainer()->getParameter('directoki.read_only')) {
            $output->writeln('Directoki is currently read only.');
            return;
        }

        $updateRecordCache = new UpdateRecordCache($this->getContainer());
        $updateFieldCache = new UpdateFieldCache($this->getContainer());


        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        foreach($doctrine->getRepository('DirectokiBundle:Project')->findAll() as $project) {
            $output->writeln('Project: '. $project->getTitle());
            foreach($doctrine->getRepository('DirectokiBundle:Directory')->findBy(array('project'=>$project)) as $directory) {
                $output->writeln('Directory: '. $directory->getTitleSingular());
                foreach($doctrine->getRepository('DirectokiBundle:Field')->findBy(array('directory'=>$directory)) as $field) {
                    $output->writeln('Field: '. $field->getPublicId());
                    $updateFieldCache->runForField($field);
                }
                foreach($doctrine->getRepository('DirectokiBundle:Record')->findBy(array('directory'=>$directory)) as $record) {
                    $output->writeln('Record: '. $record->getPublicId());
                    $updateRecordCache->go($record);
                }
            }
        }
        $output->writeln('Done!');

    }
}

