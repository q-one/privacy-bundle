<?php

/*
 * Copyright 2018-2019 Q.One Technologies GmbH, Essen
 * This file is part of QOnePrivacyBundle.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace QOne\PrivacyBundle\Command;

use QOne\PrivacyBundle\Manager\PrivacyManager;
use QOne\PrivacyBundle\Manager\PrivacyManagerInterface;
use QOne\PrivacyBundle\Obsolescence\LegacyResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This command is intended to be executed manually after database restore operations
 * to re-apply every policy operation that has happened in the meantime by using the
 * Legacy log.
 */
class LegacyCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var PrivacyManager
     */
    private $privacyManager;

    /**
     * @var array
     */
    private $assetObjectManagerNames;

    /**
     * ObsolescenceCommand constructor.
     *
     * @param PrivacyManagerInterface $privacyManager
     * @param array                   $assetObjectManagerNames
     */
    public function __construct(PrivacyManagerInterface $privacyManager, array $assetObjectManagerNames)
    {
        parent::__construct();
        $this->privacyManager = $privacyManager;
        $this->assetObjectManagerNames = $assetObjectManagerNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('privacy:legacy')
            ->setDescription('This command is intended to be executed manually after database restore operations to re-apply every policy operation that has happened in the meantime by using the Legacy log.')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'The maximum number of legacy records to process during this execution (not recommended)')
            ->addOption('om', null, InputOption::VALUE_OPTIONAL, 'The object manager to use for this command', 'default')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $limit = $counter = $input->hasOption('limit')
            ? (int) $input->getOption('limit')
            : PHP_INT_MAX;

        $om = $input->hasOption('om')
            ? $input->getOption('om')
            : $io->choice('Select the object manager to update', $this->assetObjectManagerNames);

        $appliedLegacies = 0;

        do {
            $result = $this->privacyManager->doLegacy($om);
            switch ($result->getStatus()) {
                case LegacyResult::EEOF:
                    $output->writeln(sprintf(
                        '<fg=yellow><options=bold>[@%010d]</> Processed all legacies; database of object manager "%s" is up to date.</>',
                        $result->getCurrentPosition(),
                        $om
                    ), OutputInterface::VERBOSITY_DEBUG);

                    // break the loop
                    break 2;

                case LegacyResult::ESUCCESS:
                    $output->writeln(sprintf(
                        '<fg=white><options=bold>[@%010d][#%010d]</> Successfully re-applied policy "%s" ob object %s owned by user %s; originally applied %s.</>',
                        $result->getCurrentPosition(),
                        $result->getLegacy()->getId(),
                        $result->getLegacy()->getApplicationPolicy(),
                        $result->getLegacy()->getObject(),
                        $result->getLegacy()->getUser(),
                        $result->getLegacy()->getApplicationTimestamp()->format(\DateTimeInterface::RFC3339)
                    ), OutputInterface::VERBOSITY_VERY_VERBOSE);
                    ++$appliedLegacies;
                    break;

                case LegacyResult::ENOSUCHCLASS:
                    $output->writeln(sprintf(
                        '<bg=yellow><options=bold>[@%010d][#%010d]</> Object %s owned by user %s is not managed by object manager "%s"</>',
                        $result->getCurrentPosition(),
                        $result->getLegacy()->getId(),
                        $result->getLegacy()->getObject(),
                        $result->getLegacy()->getUser(),
                        $om
                    ), OutputInterface::VERBOSITY_VERBOSE);
                    break;
            }
        } while ($counter-- > 0);

        $output->writeln(sprintf(
            '<info>Inspected <options=bold>%d</> legacies of which <options=bold>%d</> were applicable in object manager "%s".</info>',
            $limit - $counter,
            $appliedLegacies,
            $om
        ), OutputInterface::VERBOSITY_NORMAL);
    }
}
