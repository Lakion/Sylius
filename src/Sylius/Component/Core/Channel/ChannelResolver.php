<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Core\Channel;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Hostname based channel resolver.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class ChannelResolver implements ChannelResolverInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    protected $channelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        return $this->channelRepository->findMatchingHostname($request->getHost());
    }
}
