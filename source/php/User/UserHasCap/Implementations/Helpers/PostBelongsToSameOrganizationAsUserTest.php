use PHPUnit\Framework\TestCase;

class PostBelongsToSameOrganizationAsUserTest extends TestCase
{
    public function testPostBelongsToSameOrganizationTermAsUser(): void
    {
        // Create a mock of the dependencies (wpService and acfService)
        $wpServiceMock = $this->createMock(WpService::class);
        $acfServiceMock = $this->createMock(AcfService::class);

        // Set up the mock behavior for getPostTerms method
        $postId = 123;
        $expectedPostTerms = [
            (object) ['term_id' => 1],
            (object) ['term_id' => 2],
            (object) ['term_id' => 3],
        ];
        $wpServiceMock->expects($this->once())
            ->method('getPostTerms')
            ->with($postId, 'organization')
            ->willReturn($expectedPostTerms);

        // Set up the mock behavior for getField method
        $userId = 456;
        $expectedUserOrganizationTermIds = [2, 3, 4];
        $acfServiceMock->expects($this->once())
            ->method('getField')
            ->with('organizations', "user_{$userId}")
            ->willReturn($expectedUserOrganizationTermIds);

        // Create an instance of the class under test
        $helper = new PostBelongsToSameOrganizationAsUser($wpServiceMock, $acfServiceMock);

        // Call the method under test
        $result = $helper->postBelongsToSameOrganizationTermAsUser($userId, $postId);

        // Assert the expected result
        $this->assertTrue($result);
    }
}
