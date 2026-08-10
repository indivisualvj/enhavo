<?php

/*
 * This file is part of the enhavo package.
 *
 * (c) WE ARE INDEED GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enhavo\Bundle\TranslationBundle\Repository;

use Enhavo\Bundle\ResourceBundle\Repository\EntityRepository;
use Enhavo\Bundle\TranslationBundle\Model\TranslationMemoryInterface;

class TranslationMemoryRepository extends EntityRepository
{
    /**
     * The hash covers the language pair and the normalized source text, so at most one
     * entry can match. Reviewed entries are preferred anyway, in case an older data set
     * still holds duplicates from before the unique constraint was introduced.
     */
    public function findOneByHash(string $hash): ?TranslationMemoryInterface
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.hash = :hash')
            ->setParameter('hash', $hash)
            ->addOrderBy('m.status', 'DESC')
            ->addOrderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
