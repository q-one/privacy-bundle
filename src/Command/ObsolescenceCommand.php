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
use QOne\PrivacyBundle\Mapping\MetadataRegistry;
use QOne\PrivacyBundle\Mapping\MetadataRegistryInterface;
use QOne\PrivacyBundle\Obsolescence\ObsolescenceResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * This command uses the PrivacyManager to evaluate the Assets in descending order of which hasn't
 * been evaluated the longest, and therefore triggering the necessary policies.
 *
 * The command supports atomic and chunk sized processing so that there may
 *  - be several instances running at the same time
 *  - unexpected aborts (e.g. PHP segfaults, server shuts down) don't lead to an unexpected result and
 *    the process may be continued without hesitation.
 */
class ObsolescenceCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var PrivacyManager
     */
    private $privacyManager;

    /**
     * @var MetadataRegistry
     */
    private $metadataRegistry;

    /**
     * @var array
     */
    private $assetObjectManagerNames;

    /**
     * ObsolescenceCommand constructor.
     *
     * @param PrivacyManagerInterface   $privacyManager
     * @param MetadataRegistryInterface $metadataRegistry
     * @param array                     $assetObjectManagerNames
     */
    public function __construct(
        PrivacyManagerInterface $privacyManager,
        MetadataRegistryInterface $metadataRegistry,
        array $assetObjectManagerNames
    ) {
        parent::__construct();

        $this->privacyManager = $privacyManager;
        $this->metadataRegistry = $metadataRegistry;
        $this->assetObjectManagerNames = $assetObjectManagerNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('privacy:obsolescence')
            ->setDescription('This command evaluates the Assets in descending order of which has not been evaluated the longest, and therefore triggering the necessary policies')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'The maximum number of assets to process during this execution', 16)
            ->addOption('om', null, InputOption::VALUE_OPTIONAL, 'The object manager to use for this command', 'default')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $counter = (int) $input->getOption('limit');
        $om = $input->hasOption('om')
            ? $input->getOption('om')
            : $io->choice('Select the object manager to update', $this->assetObjectManagerNames);
        $obsoletedAssets = 0;

        do {
            $result = $this->privacyManager->doObsolescence($om);
            switch ($result->getStatus()) {
                case ObsolescenceResult::EEOF:
                    $output->writeln(sprintf(
                        '<fg=yellow>Assets of database of object manager "%s" are fully processed.</>',
                        $om
                    ), OutputInterface::VERBOSITY_DEBUG);

                    // break the loop
                    break 2;

                case ObsolescenceResult::ESUCCESS:
                    if ($result->hasBeenObsoleted()) {
                        $statusStr = 'OBSOLETED  ';
                        $statusColor = 'red';
                    } else {
                        $statusStr = 'APPROPRIATE';
                        $statusColor = 'green';
                    }

                    $output->writeln(sprintf(
                        '<fg=white><bg=%s;options=bold>%s</> Successfully applied policy "%s" of group %s::%s owned by user %s at %s; Legacy is %010d.</>',
                        $statusColor,
                        $statusStr,
                        $result->getLegacy()->getApplicationPolicy(),
                        $result->getAsset()->getObject(),
                        $result->getAsset()->getGroupId(),
                        $result->getAsset()->getUser(),
                        $result->getLegacy()->getApplicationTimestamp()->format(\DateTimeInterface::RFC3339),
                        $result->getLegacy()->getId()
                    ), OutputInterface::VERBOSITY_VERY_VERBOSE);
                    ++$obsoletedAssets;
                    break;

                case ObsolescenceResult::ENOSUCHCLASS:
                    $output->writeln(sprintf(
                        '<fg=white><bg=yellow;fg=black;options=bold>NOSUCHCLASS</> Object %s owned by user %s is not managed by object manager "%s"</>',
                        $result->getAsset()->getObject(),
                        $result->getAsset()->getUser(),
                        $om
                    ), OutputInterface::VERBOSITY_VERBOSE);
                    break;
            }
        } while ($counter-- > 0);

        $output->writeln(sprintf(
            '<info>Inspected <options=bold>%d</> legacies of which <options=bold>%d</> were applicable in object manager "%s".</info>',
            $limit - $counter,
            $obsoletedAssets,
            $om
        ), OutputInterface::VERBOSITY_NORMAL);
    }
}
