<?php
namespace presentator\api\tests;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;

   /**
    * Checks if a response has generic HTTP Unauthorized fingerprint.
    */
   public function seeUnauthorizedResponse()
   {
       $this->seeResponseCodeIs(401);
       $this->seeResponseIsJson();
       $this->seeResponseMatchesJsonType([
           'message' => 'string',
           'errors'  => 'array',
       ]);
   }

   /**
    * Checks if a response has generic HTTP Forbidden fingerprint.
    */
   public function seeForbiddenResponse()
   {
       $this->seeResponseCodeIs(403);
       $this->seeResponseIsJson();
       $this->seeResponseMatchesJsonType([
           'message' => 'string',
           'errors'  => 'array',
       ]);
   }

   /**
    * Checks if a response has generic HTTP Not found fingerprint.
    */
   public function seeNotFoundResponse()
   {
       $this->seeResponseCodeIs(404);
       $this->seeResponseIsJson();
       $this->seeResponseMatchesJsonType([
           'message' => 'string',
           'errors'  => 'array',
       ]);
   }

   /**
    * Checks if a response has generic HTTP Bad request fingerprint.
    */
   public function seeBadRequestResponse()
   {
       $this->seeResponseCodeIs(400);
       $this->seeResponseIsJson();
       $this->seeResponseMatchesJsonType([
           'message' => 'string',
           'errors'  => 'array',
       ]);
   }

   /**
    * Checks if a response contains common user auth fields.
    *
    * @param array [$userData] Optionally user data to check.
    */
   public function seeUserAuthResponse(array $userData = [])
   {
       $this->seeResponseIsJson();
       $this->seeResponseMatchesJsonType([
           'token' => 'string',
           'user'  => [
               'id'       => 'integer',
               'email'    => 'string',
               'avatar'   => 'array',
               'settings' => 'array',
           ],
       ]);
       $this->dontSeeResponseContainsUserHiddenFields('user');
       $this->seeResponseContainsJson(['user' => $userData]);
   }

   /**
    * Checkes whether the response doesn't have user hidden fields in it.
    *
    * @param string [$subPath] JSON Subpath that will be prepended before the field key.
    */
   public function dontSeeResponseContainsUserHiddenFields(string $subPath = '')
   {
       $this->dontSeeResponseJsonMatchesJsonPath('$.' . ($subPath ? ($subPath . '.') : '') . 'password');
       $this->dontSeeResponseJsonMatchesJsonPath('$.' . ($subPath ? ($subPath . '.') : '') . 'authKey');
       $this->dontSeeResponseJsonMatchesJsonPath('$.' . ($subPath ? ($subPath . '.') : '') . 'passwordHash');
       $this->dontSeeResponseJsonMatchesJsonPath('$.' . ($subPath ? ($subPath . '.') : '') . 'passwordResetToken');
       $this->dontSeeResponseJsonMatchesJsonPath('$.' . ($subPath ? ($subPath . '.') : '') . 'avatarFilePath');
   }

   /**
    * Sends and checks data provider response(s) against multiple test scenarios.
    *
    * @param string        $url                The request url to test.
    * @param array         $testScenarios      Array list with test scenarios in the following format: `[['params' => array, 'expected' => array], ...]`.
    *                                          The expected array could be a list of `\yii\db\ActiveRecord` models or just plain IDs.
    * @param Callable|null [$scenarioCallback] Optional callback `function ($scenarioIndex, $scenarioData)` that is executed after each test scenario.
    */
   public function sendAndCheckDataProviderResponses(string $url, array $testScenarios, Callable $scenarioCallback = null)
   {
       $reflection = new \ReflectionProperty('\yii\data\BaseDataProvider', 'counter');
       $reflection->setAccessible(true);

       foreach ($testScenarios as $scenarioIndex => $scenarioData) {
           $reflection->setValue(null, 0); // reset data provider counter before each test

           $this->amGoingTo("test scenario {$scenarioIndex}");
           $this->sendGET($url, $scenarioData['params'] ?? []);
           $this->seeResponseCodeIs(200);
           $this->seeResponseIsJson();
           $this->seeHttpHeader('X-Pagination-Total-Count');
           $this->seeHttpHeader('X-Pagination-Page-Count');
           $this->seeHttpHeader('X-Pagination-Per-Page');
           $this->seeHttpHeader('X-Pagination-Current-Page');

           if (empty($scenarioData['expected'])) {
               $this->dontSeeResponseJsonMatchesJsonPath('$.*.id');
           } else {
               foreach ($scenarioData['expected'] as $i => $model) {
                   $id = $model instanceof \yii\db\ActiveRecord ? $model->id : $model;

                   $this->amGoingTo("ensure the existence and correct order of a test model {$i} in scenario {$scenarioIndex}");
                   $this->seeResponseMatchesJsonType(['id' => 'integer:=' . $id], '$.' . $i);

                   if (isset($scenarioData['fields'])) {
                       $this->seeResponseMatchesJsonType($scenarioData['fields'], '$.' . $i);
                   }
               }
           }

           if ($scenarioCallback !== null) {
               $scenarioCallback($scenarioIndex, $scenarioData);
           }
       }
   }
}
