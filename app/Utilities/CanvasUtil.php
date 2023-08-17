<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Http\Controllers\LtiController;

class CanvasUtil extends ProviderUtil
{
    /*
   * From Canvas, we get this information, this will be useful
   * because even though we are not speaking to canvas via LTI, we
   * will be modeling our providers after the LTI model.
   ***********************************************************
  the ID of the Account object
  "id": 2,
   The display name of the account
  "name": "Canvas Account",
   The UUID of the account
  "uuid": "WvAHhY5FINzq5IyRIJybGeiXyFkG3SqHUPb7jZY5",
   The account's parent ID, or null if this is the root account
  "parent_account_id": 1,
   The ID of the root account, or null if this is the root account
  "root_account_id": 1,
   The state of the account. Can be 'active' or 'deleted'.
  "workflow_state": "active"
}
*/
    // This probably will not remain as we will not be speaking LTI
    // to canvas, but we may want their provider info stored in similar schema
    public function __construct()
    {
        $controller = new LtiController();
        $info = $controller->getCanvasLtiAccount();
        $this->ltiAccount = $info['root_account_id'];
        $this->providerId = $info['id'];
        $this->providerName = $info['name'];
    }

    /**
     * Get a list of users from Canvas
     *
     * @param? string
     * @returns JSON decoded
     * @throws \Exception
     *
     * AccountId can be accessed via the field in the class,
     * but it seems most of the time it will be self.
     */
    public static function listUsers($accountId = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $accessToken = env('CANVAS_API_KEY');

        $client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        try {
            $response = $client->get($baseUrl . 'accounts/' . $accountId . 'users');

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {

                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }

    /* Get details for a specific user in Canvas
     *
     * @param int $userId
     * @return JSON decoded
     * @throws \Exception
     */
    public static function showUserDetails($userId)
    {
        $baseUrl = env('CANVAS_API');
        $accessToken = env('CANVAS_API_KEY');

        $client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' . $userId);

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {

            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Create a new user in Canvas
     *
     * @param string $name
     * @param string $email
     * @param? boolean $terms (defaults true)
     * @return JSON decoded
     * @throws \Exception
     */

    public function createUser(string $name, string $email, bool $terms = true)
    {

        $userData = [
            'user' => [
                'name' => $name,
                'skip_registration' => true,
                'terms_of_use' => $terms
            ],
            'pseudonym' => [
                'unique_id' => $email,
                'send_confirmation' => false,
            ],
            'force_validations' => true,
        ];
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->post($baseUrl . "accounts/self/users/", $userData);

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Activity Stream
     *
     * @param? string $account (default self)
     * @return JSON decoded
     * @throws \Exception
     */
    public static function listActivityStream($account = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' . $account . '/activity_stream');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Activity Stream Summary from Canvas
     * @param? string $account (default self)
     * @return JSON decoded
     * @throws \Exception
     */
    public static function getActivityStreamSummary($account = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' .  $account . '/activity_stream/summary');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Todo Items from Canvas
     * @param? string $account (default self)
     * @return JSON decoded
     * @throws \Exception
     */
    public static function listTodoItems($account = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' .  $account . '/todo');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * Get Todo Items Count from Canvas
     * @param? string $account (default self)
     * @return JSON decoded
     *
     **/
    public static function getTodoItemsCount($account = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' .  $account . '/todo_item_count');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Upcoming Assignments from Canvas
     * @param? string $account (default self)
     * @return JSON decoded
     * @throws \Exception
     */

    public static function listUpcomingAssignments($userId = 'self/')
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' .  $userId . '/upcoming_events');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Missing Submissions from Canvas
     * @param? string $account (default self)
     * @return JSON decoded
     * @throws \Exception
     */
    public static function listMissingSubmissions($userId)
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' .  $userId . '/missing_submissions');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Courses from Canvas
     * @return JSON decoded
     * @throws \Exception
     **/
    public static function listCourses()
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'courses');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Courses from Canvas per User
     * @param string $userId
     * @return JSON decoded
     * @throws \Exception
     **/
    public static function listCoursesForUser($userId)
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');

        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'users/' . $userId . '/courses');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * List Course Assignments from Canvas
     *
     * @param string $userId (default self)
     * @param string $courseId
     * @return JSON decoded
     * @throws \Exception
     *
     * Canvas Docs:
     * "You can supply self as the user_id to query your own progress
     * in a course. To query another user’s progress, you must be a
     * teacher in the course, an administrator, or a linked observer of the user."
     * */
    public static function getUserCourseProgress($userId = 'self/', $courseId)
    {
        $baseUrl = env('CANVAS_API');
        $apiKey = env('CANVAS_API_KEY');
        $client = new Client([
            'Authorization' => 'Bearer ' . $apiKey,
        ]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/users/' . $userId . 'progress');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Course Assignments from Canvas
     * @param string $userId
     * @returns JSON decoded
     * @throws \Exception
     * */
    public static function getEnrollmentsByUser($userId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);

        try {
            $response = $client->get($baseUrl . 'users/' . $userId . '/enrollments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Course Assignments from Canvas
     * @param string $userId
     * @returns JSON decoded
     * @throws \Exception
     **/
    public static function getEnrollmentsByCourse($courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/enrollments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Course Enrollments By Section
     * @param string $sectionId
     * @returns JSON decoded
     * @throws \Exception
     **/
    public static function getEnrollmentsBySection($sectionId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'sections/' . $sectionId . '/enrollments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Enroll a user in a course
     *
     * @param string $user_id
     * @param string $course_id
     * @param? string $type (default=StudentEnrollment)
     *
     **/
    public static function enrollUser($userId, $type = "StudentEnrollment", $courseId)
    {
        $enrollment = [
            'user_id' => [$userId],
            'type' => [$type],
        ];

        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/enrollments' . $enrollment);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     *  Enroll a user in a Section
     * @param string $sectionId
     * @param string $user_id (default=self)
     * @param? string $type (default=StudentEnrollment)
     * @return decoded JSON
     * @throws Exception
     **/
    public static function enrollUserInSection($sectionId, $userId, $type = "StudentEnrollment")
    {
        $enrollment = [
            'user_id' => [$userId],
            'type' => [$type],
        ];
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'sections/' . $sectionId . '/enrollments' . $enrollment);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     *  Delete a course Enrollment
     * @param string $course_id
     * @param string $user_id (default=self)
     * @return decoded JSON
     * @throws Exception
     */
    public static function deleteEnrollment($enrollmentId, $courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->delete($baseUrl . 'courses/' . $courseId . '/enrollments/' . $enrollmentId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Accept a course invitation
     * @param string $course_id
     * @param string $user_id (default=self)
     * @return decoded JSON
     * @throws Exception
     */
    public static function acceptCourseInvitation($courseId, $userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/enrollments/' . $userId . 'accept');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Reject a course invitation
     * @param string $course_id
     * @param string $user_id (default=self)
     * @return decoded JSON
     * @throws Exception
     */
    public static function rejectCourseInvitation($courseId, $userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/enrollments/' . $userId . 'reject');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Reactivate a course enrollment
     * @param string $courseId
     * @param string $userId (default=self)
     * @return decoded JSON
     * @throws Exception
     **/
    public static function reactivateCourseEnrollment($courseId, $userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/enrollments/' . $userId . 'reactivate');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Add last attended date of course
     * @param string $courseId
     * @param string $userId (default=self)
     * @return decoded JSON
     * @throws Exception
     **/
    public static function addLastAttendedDate($courseId, $userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/enrollments/' . $userId . 'last_attended');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * Query progress of user
     * @param string $userId (default=self)
     * @return decoded JSON
     * @throws Exception
     **/
    public static function queryUserProgress($userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'progress/' . $userId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Cancel user progress
     * @param string $userId (default=self)
     * @return decoded JSON
     * @throws Exception
     **/
    public static function cancelUserProgress($userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'progress/' . $userId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Assignments for User
     * @param string $userId (default=self)
     * @param string $coursrId
     * @return decoded JSON
     * @throws Exception
     **/
    public static function listAssignmentsForUser($courseId, $userId = 'self/')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        if ($userId[strlen($userId) - 1] != '/') {
            $userId .= '/';
        }
        try {
            $response = $client->get($baseUrl . 'users/' . $userId . 'courses' . $courseId . '/assignments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Assignments for Course
     * @param string $courseId
     * @return decoded JSON
     * @throws Exception
     **/
    public static function listAssignmentsByCourse($courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        if ($courseId[strlen($courseId) - 1] != '/') {
            $courseId .= '/';
        }
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . 'assignments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * List Assignments for Course
     * @param string $courseId
     * @return decoded JSON
     * @throws Exception
     **/
    public static function listAssignmentGroupsByCourse($assignmentGroupId, $courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignment_groups/' . $assignmentGroupId . '/assignments');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /**
     * Delete Assignment
     * @param string $courseId
     * @param string $assignmentId
     * @return decoded JSON
     * @throws Exception
     **/
    public static function deleteAssignment($courseId, $assignmentId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->delete($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /**
     * Get a single Assignment
     * @param string $courseId
     * @param string $assignmentId
     * @return decoded JSON
     * @throws Exception
     **/
    public static function getAssignment($courseId, $assignmentId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Create an assignment for a given Course ID
        *
        * There are so many possible parameters, but the only one required
        * is "name" so we will just pass in the array which can have any
        * or all of them
        * @param associative array $assignmentInfo
        * @param string $courseId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function createAssignmentForCourse($assignmentInfo, $courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/assignments' . $assignmentInfo);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /*
        * Edit an assignment for a given Course ID
        *
        * There are so many possible parameters, but the only one required
        * is "name" so we will just pass in the array which can have any
        * or all of them
        * @param associative array $assignmentInfo
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function editAssignmentForCourse($assignmentInfo, $courseId, $assignmentId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/assignments' . $assignmentId . '/' . $assignmentInfo);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Edit an assignment for a given Course ID
        *
        * There are so many possible parameters, but the only one required
        * is "name" so we will just pass in the array which can have any
        * or all of them
        * @param associative array $assignment (could also be a file? TODO: look into submissions)
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function submitAssignment($courseId, $assignmentId, $assignment)
    {

        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submissions' . $assignment);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * List assignment submissions for a given Course ID
        *
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function getAssignmentSubmissions($courseId, $assignmentId, $assignment)
    {

        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submissions');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * List submissions for multiple assignments for a given Course ID
        *
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function getSubmissionsForMultipleAssignments($courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/students' .  '/submissions');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Get single submission for user / assignment
        *
        * @param string $courseId
        * @param string $userId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function getSubmissionForUser($courseId, $assignmentId, $userId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/submissions' . $userId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Get single submission by anonymous ID
        *
        * @param string $courseId
        * @param string $anonId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function getSubmissionForAnonID($courseId, $assignmentId, $anonId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/anonymous_submissions' . $anonId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Upload a file for submission
        *
        * @param string $courseId
        * @param string $userId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function uploadFileForSubmission($courseId, $assignmentId, $userId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->post($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/submissions' . $userId . '/files');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Grade or comment on a submission
        *
        * @param string $courseId
        * @param string $userId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function gradeOrCommentSubmission($courseId, $assignmentId, $userId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/submissions' . $userId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Grade or comment on a submission by anonymous ID
        *
        * @param string $anonId
        * @param string $assignmentId
        * @param string $courseId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function gradeOrCommentSubmissionAnon($courseId, $assignmentId, $anonId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/anonymous_submissions' . $anonId);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * List Gradeable Students
        *
        * @param string $assignmentId
        * @param string $courseId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function listGradeableStudents($courseId, $assignmentId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments' .  $assignmentId . '/gradeable_students');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * List Multiple Assignments Gradeable Students
        *
        * @param string $courseId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function listMultipleAssignmentsGradeableStudents($courseId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments' . '/gradeable_students');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Mark Submision as read
        *
        * @param string $userId
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function markSubmissionAsRead($courseId, $assignmentId, $userId = '/self')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        if ($userId[strlen($userId) - 1] != '/') {
            $userId .= '/';
        }
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submissions' . $userId . '/read');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Mark Submision Item as read
        *
        * @param string $userId
        * @param string $item
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function markSubmissionItemAsRead($courseId, $assignmentId, $userId = '/self', $item)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        if ($userId[strlen($userId) - 1] != '/') {
            $userId .= '/';
        }
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submissions' . $userId . '/read' . $item);
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Mark Submision as unread
        *
        * @param string $userId
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function markSubmissionAsUnread($courseId, $assignmentId, $userId = '/self')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        if ($userId[strlen($userId) - 1] != '/') {
            $userId .= '/';
        }
        try {
            $response = $client->delete($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submissions' . $userId . '/read');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }

    /*
        * Clear unread status for all Submisions
        * Site admin only
        * @param string $userId
        * @param string $courseId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function clearUnreadStatusForAllSubmissions($courseId, $userId = '/self')
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->put($baseUrl . 'courses/' . $courseId . '/submissions/' . $userId . '/clear_unread');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
    /*
        * Get Submision summary
        *
        * @param string $courseId
        * @param string $assignmentId
        * @return decoded JSON
        * @throws Exception
        **/
    public static function getSubmissionSummary($courseId, $assignmentId)
    {
        $baseUrl = env("CANVAS_API");
        $apiKey = env("CANVAS_API_KEY");
        $client = new Client(['Authorization' => 'Bearer' . $apiKey]);
        try {
            $response = $client->get($baseUrl . 'courses/' . $courseId . '/assignments/' . $assignmentId . '/submission_summary');
            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            } else {
                throw new \Exception('API request failed with status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $error) {
            throw new \Exception('API request failed: ' . $error->getMessage());
        }
    }
}
