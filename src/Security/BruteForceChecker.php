<?php


namespace App\Security;


use App\Repository\AuthLogRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\RequestStack;

class BruteForceChecker
{
    /** @var RequestStack $requestStack */
    private $requestStack;

    /** @var AuthLogRepository $authLogRepository */
    private $authLogRepository;

    public function __construct(
        RequestStack $requestStack,
        AuthLogRepository $authLogRepository
    )
    {
        $this->requestStack = $requestStack;
        $this->authLogRepository = $authLogRepository;
    }

    /**
     * Add a failed authenticated attempt.
     * Add into the blacklisting if failed authenticated attempt is equal to 5 attempts
     *
     * @param string $emailEntered
     * @param string|null $userIp
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function addFailedAuthAttempt(string $emailEntered, ?string $userIp): void
    {
        if ($this->authLogRepository->isBlackListedWithThisAttemptFailure($emailEntered, $userIp)) {
            $this->authLogRepository->addFailedAuthAttempt($emailEntered, $userIp, true);
        } else {
            $this->authLogRepository->addFailedAuthAttempt($emailEntered, $userIp);
        }
    }

    /**
     * Return the end of blacklisting rounded up to the next minute or null
     *
     * @return string|null If end of blacklisting is 15:02:42, it will return 15:03
     *
     * @throws NonUniqueResultException
     */
    public function getEndOfBlackListing(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        $userIp = $request->getClientIp();
        $emailEntered = $request->request->get('email');
        return $this->authLogRepository->getEndOfBlackListing($emailEntered, $userIp);
    }
}