<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\Repository;

// namespace GeorgRinger\LoginLink\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Georg Ringer
 */
class TokenRepository
{
    private const TABLE = 'tx_loginlink_token';
    private const TOKEN_VALIDITY = 60 * 5;

    public function getTokenRow(string $token, string $authType, bool $clearToken = false): ?array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $row = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->eq('token', $queryBuilder->createNamedParameter($token)),
                $queryBuilder->expr()->eq('auth_type', $queryBuilder->createNamedParameter($authType)),
                $queryBuilder->expr()->gte('valid_until', time())
            )
            ->execute()
            ->fetchAssociative();
        if (is_array($row)) {
            $userId = (int)$row['user_uid'];
            if ($clearToken) {
                $this->removeByUserId($userId, $authType);
            }
            return $row;
        }
        return null;
    }

    protected function getConnection(): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);
    }

    public function removeByUserId(int $userId, string $authType): void
    {
        $this->getConnection()->delete(
            self::TABLE,
            [
                'user_uid' => $userId,
                'auth_type' => $authType,
            ]
        );
    }

    public function removeOutdated(): void
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->delete(self::TABLE)
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->lt('valid_until', time())
            )
            ->execute();
    }

    public function clearAll(): void
    {
        $this->getConnection()->truncate(self::TABLE);
    }

    public function add(int $userId, string $authType, string $token, int $invokedBy): void
    {
        $this->removeByUserId($userId, $authType);
        $this->getConnection()->insert(
            self::TABLE,
            [
                'user_uid' => $userId,
                'auth_type' => $authType,
                'token' => $token,
                'valid_until' => time() + self::TOKEN_VALIDITY,
                'invoked_by' => $invokedBy,
            ]
        );
    }
}
