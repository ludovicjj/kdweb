<?php

namespace App\Repository;

use App\Entity\AuthLog;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

/**
 * @method AuthLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthLog[]    findAll()
 * @method AuthLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @extends ServiceEntityRepository<AuthLog>
 */
class AuthLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthLog::class);
    }

    public const BLACK_LISTING_MAX_DELAY_IN_MINUTES = 15;
    public const MAX_FAILED_AUTH_ATTEMPTS = 5;

    /**
     * Add a failed authentication attempt.
     *
     * @param string $emailEntered
     * @param string|null $userIp
     * @param bool $isBackListed
     */
    public function addFailedAuthAttempt(
        string $emailEntered,
        ?string $userIp,
        bool $isBackListed = false
    ): void
    {
        $authLog = (new AuthLog($emailEntered, $userIp))->setIsSuccessfulAuth(false);
        if ($isBackListed) {
            $start = new DateTimeImmutable('now');
            $end = $start->modify(sprintf('+%d minutes', self::BLACK_LISTING_MAX_DELAY_IN_MINUTES));
            $authLog
                ->setStartBlackListingAt($start)
                ->setEndBlackListingAt($end);
        }
        try {
            $this->_em->persist($authLog);
            $this->_em->flush();
        } catch (ORMException|OptimisticLockException $e) {
        }
    }

    /**
     * Add a successful authentication attempt.
     *
     * @param string $emailEntered
     * @param string|null $userIp
     * @param bool $isRememberMeAuth
     */
    public function addSuccessfulAttempt(
        string $emailEntered,
        ?string $userIp,
        bool $isRememberMeAuth = false
    ): void
    {
        $authLog = new AuthLog($emailEntered, $userIp);
        $authLog
            ->setIsSuccessfulAuth(true)
            ->setIsRememberMeAuth($isRememberMeAuth);

        try {
            $this->_em->persist($authLog);
            $this->_em->flush();
        } catch (ORMException|OptimisticLockException $e) {
        }
    }

    /**
     * Return the number of recent failed authenticated attempt
     *
     * @param string $emailEntered
     * @param string|null $userIp
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getRecentFailedAuthAttempt(string $emailEntered, ?string $userIp): int
    {
        $datetime = new DateTimeImmutable('now');
        $beforeAt = $datetime->modify(sprintf('-%d minutes', self::BLACK_LISTING_MAX_DELAY_IN_MINUTES));

        return $this->createQueryBuilder('af')
            ->select('COUNT(af)')
            ->where('af.authAttemptAt >= :datetime')
            ->andWhere('af.userIp = :user_ip')
            ->andWhere('af.emailEntered = :email_entered')
            ->andWhere('af.isSuccessFulAuth = false')
            ->setParameters([
                "datetime" => $beforeAt,
                "user_ip" => $userIp,
                "email_entered" => $emailEntered
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Return if user is black listed
     *
     * @param string $emailEntered
     * @param string|null $userIp
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function isBlackListedWithThisAttemptFailure(string $emailEntered, ?string $userIp): bool
    {
        return $this->getRecentFailedAuthAttempt($emailEntered, $userIp) >= self::MAX_FAILED_AUTH_ATTEMPTS -2;
    }

    /**
     * Return the last entry in the blacklist using the userIp/email pair if it exist.
     *
     * @param string $emailEntered
     * @param string|null $userIp
     *
     * @return AuthLog|null
     *
     * @throws NonUniqueResultException
     */
    public function getLastEntryBlackListed(string $emailEntered, ?string $userIp): ?AuthLog
    {
        $datetime = new DateTimeImmutable('now');
        $beforeAt = $datetime->modify(sprintf('-%d minutes', self::BLACK_LISTING_MAX_DELAY_IN_MINUTES));

        return $this->createQueryBuilder('al')
            ->select('al')
            ->where('al.userIp = :user_ip')
            ->andWhere('al.emailEntered = :email_entered')
            ->andWhere('al.endBlackListingAt IS NOT NULL')
            ->andWhere('al.endBlackListingAt >= :datetime')
            ->setParameters([
                'user_ip' => $userIp,
                'email_entered' => $emailEntered,
                'datetime' => $beforeAt
            ])
            ->orderBy('al.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return the end of blacklisting rounded to the next minute
     *
     * @param string $emailEntered
     * @param string|null $userIp
     *
     * @return string|null Time with the following format: 15h00
     *
     * @throws NonUniqueResultException
     */
    public function getEndOfBlackListing(string $emailEntered, ?string $userIp): ?string
    {
        $blackListing = $this->getLastEntryBlackListed($emailEntered, $userIp);

        if (!$blackListing || $blackListing->getEndBlackListingAt() === null) {
            return null;
        }

        return $blackListing->getEndBlackListingAt()->add(new DateInterval("PT1M"))->format('H\hi');
    }
}
