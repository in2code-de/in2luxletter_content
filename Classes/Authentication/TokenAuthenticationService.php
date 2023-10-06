<?php

declare(strict_types=1);

namespace In2code\In2luxletterContent\Authentication;

// namespace GeorgRinger\LoginLink\Authentication;

use In2code\In2luxletterContent\Repository\TokenRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Georg Ringer
 */
class TokenAuthenticationService extends AbstractAuthenticationService
{
    /**
     * @var TokenRepository
     */
    protected $tokenRepository;

    public function __construct()
    {
        $this->tokenRepository = new TokenRepository();
    }

    public function getUser()
    {
        $token = $this->getTokenFromRequest();
        if (!$token) {
            return false;
        }
        $this->tokenRepository->removeOutdated();
        $tokenRow = $this->tokenRepository->getTokenRow($token, strtolower($this->authInfo['loginType']));
        if ($tokenRow) {
            return BackendUtility::getRecord($this->authInfo['db_user']['table'], $tokenRow['user_uid']);
        }

        return false;
    }

    protected function getTokenFromRequest(): ?string
    {
        if ((new Typo3Version())->getMajorVersion() >= 12) {
            /** @var ServerRequest $request */
            $request = $this->authInfo['request'] ?? $GLOBALS['TYPO3_REQUEST'];
            return $request->getQueryParams()['byToken'] ?? null;
        }
        return trim((string)(GeneralUtility::_GET('byToken')));
    }

    public function authUser(array $user): int
    {
        $token = $this->getTokenFromRequest();
        if ($token) {
            $loginType = strtolower($this->authInfo['loginType']);
            $tokenRow = $this->tokenRepository->getTokenRow($token, $loginType, true);
            if ($tokenRow && $tokenRow['user_uid'] === $user['uid']) {
                //if ($loginType === 'be') {
                //$this->setSwitchbackInformation();
                //}
                return 200;
            }
        }
        return 110;
    }
}
