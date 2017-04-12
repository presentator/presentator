<?php
namespace api\tests;

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

    /**
     * Helper action for handling common unauthorized access tests.
     * @param string $url
     * @param string $method
     */
    public function seeUnauthorizedAccess($url, $method = 'GET')
    {
        $this->deleteHeader('X-Access-Token');
        $this->{'send' . strtoupper($method)}($url);
        $this->seeResponseCodeIs(401);
        $this->seeResponseIsJson();
        $this->seeResponseMatchesJsonType([
            'message' => 'string',
            'errors'  => 'array',
        ]);
    }
}
