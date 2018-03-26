<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Action\EmailModeratorReport;
use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 *
 * This command is specifically for sending moderator report to arbitary email address, such as an email list.
 *
 * It does not send to the project admins automatically (other code [that hasn't been written yet] does that).
 *
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class EmailModeratorReportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:email-moderator-report')
            ->setDescription('Email Moderator Report')
            ->setHelp('This command emails a moderator report.')
            ->addArgument('project', InputArgument::REQUIRED, 'project')
            ->addArgument('email', InputArgument::REQUIRED, 'email')
            ->addOption(
                'emailFrom',
                null,
                InputOption::VALUE_REQUIRED,
                'Email from?',
                1
            );

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

        $emailFrom = $input->getOption('emailFrom');
        if (!$emailFrom || !filter_var($emailFrom, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('No Email From');
            return;
        }
        $output->writeln('Email From: '. $emailFrom);

        $emailTo = $input->getArgument('email');
        if (!$emailTo || !filter_var($emailTo, FILTER_VALIDATE_EMAIL)) {
            $output->writeln('No Email To');
            return;
        }
        $output->writeln('Email To: '. $emailTo);

        $action = new EmailModeratorReport($this->getContainer(), $project);

        $records = $action->getRecordsToList();
        if (!$records) {
            $output->writeln('Nothing to Send');
            return;
        }

        $message = "The following records need attention: \n\n";

        foreach($records as $record) {

            $message .= $this->getContainer()->get('router')->generate('directoki_admin_project_directory_record_show', array(
                'recordId'=>$record->getPublicId(),
                'directoryId'=>$record->getDirectory()->getPublicId(),
                'projectId'=>$record->getDirectory()->getProject()->getPublicId(),
                ))."\n\n";
        }

        $output->writeln('Sending ...');

        $message = (new \Swift_Message('Moderator Report for '.$project->getTitle()))
            ->setFrom($emailFrom)
            ->setTo($emailTo)
            ->setBody(
                $message,
                'text/plain'
            );

        $this->getContainer()->get('mailer')->send($message);

    }

}
