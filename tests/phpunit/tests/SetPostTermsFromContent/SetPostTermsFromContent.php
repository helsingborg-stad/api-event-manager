<?php

namespace EventManager\Tests\SetPostTermsFromContent;

use EventManager\SetPostTermsFromContent\SetPostTermsFromContent;
use Mockery;
use WP_Mock\Tools\TestCase;
use WpService\WpService;

class SetPostTermsFromContentTest extends TestCase
{
    /**
     * @testdox callback for action hook save_post is added
     */
    public function testCallbackForActionHookSavePostIsAdded()
    {
        $tagReader   = $this->createMock('EventManager\TagReader\TagReaderInterface');
        $wpService   = $this->createMock(WpService::class);
        $sutInstance = new SetPostTermsFromContent('post', 'category', $tagReader, $wpService);

        $wpService->expects($this->once())->method('addAction')->with('save_post', [$sutInstance, 'setPostTermsFromContent']);

        $sutInstance->addHooks();
    }

    /**
     * @testdox update() adds tags found in content to post
     */
    public function testUpdateAddsTagsFoundInContentToPost()
    {
        $postContent = 'content #tag1';
        $post        = $this->mockPost(['ID' => 1, 'post_content' => $postContent, 'post_type' => 'post']);
        $tagReader   = Mockery::mock('EventManager\TagReader\TagReaderInterface');
        $wpService   = Mockery::mock(WpService::class);

        $tagReader->shouldReceive('getTags')->once()->with($post->post_content)->andReturn(['tag1']);
        $wpService->shouldReceive('getPost')->once()->andReturn($post);
        $wpService->allows('applyFilters')->once()->andReturnArg(2);
        $wpService->shouldReceive('termExists')->once()->andReturn(true);
        $wpService->shouldReceive('setPostTerms')->once()->with(1, ['tag1'], 'category', false);

        $sutInstance = new SetPostTermsFromContent('post', 'category', $tagReader, $wpService);
        $sutInstance->setPostTermsFromContent($post->ID);

        $this->assertConditionsMet();
    }

    /**
     * @testdox update() inserts tags that does not exist
     */
    public function testUpdateInsertsTagsThatDoesNotExist()
    {
        $postContent = 'content #tag1';
        $post        = $this->mockPost(['ID' => 1, 'post_content' => $postContent, 'post_type' => 'post']);
        $tagReader   = Mockery::mock('EventManager\TagReader\TagReaderInterface');
        $wpService   = Mockery::mock(WpService::class);

        $tagReader->shouldReceive('getTags')->once()->with($post->post_content)->andReturn(['tag1']);
        $wpService->shouldReceive('getPost')->once()->andReturn($post);
        $wpService->allows('applyFilters')->once()->andReturnArg(2);
        $wpService->shouldReceive('termExists')->once()->with('tag1', 'category')->andReturn(false);
        $wpService->shouldReceive('insertTerm')->once()->with('tag1', 'category');
        $wpService->shouldReceive('setPostTerms')->once()->with(1, ['tag1'], 'category', false);

        $sutInstance = new SetPostTermsFromContent('post', 'category', $tagReader, $wpService);
        $sutInstance->setPostTermsFromContent($post->ID);

        $this->assertConditionsMet();
    }

    /**
     * @testdox update() passes empty array to setPostTerms() if no tags are found in content
     */
    public function testUpdatePassesEmptyArrayToSetPostTermsIfNoTagsAreFoundInContent()
    {
        $postContent = 'content';
        $post        = $this->mockPost(['ID' => 1, 'post_content' => $postContent, 'post_type' => 'post']);
        $tagReader   = Mockery::mock('EventManager\TagReader\TagReaderInterface');
        $wpService   = Mockery::mock(WpService::class);

        $tagReader->shouldReceive('getTags')->once()->with($post->post_content)->andReturn([]);
        $wpService->shouldReceive('getPost')->once()->andReturn($post);
        $wpService->allows('applyFilters')->once()->andReturnArg(2);
        $wpService->shouldReceive('termExists')->never();
        $wpService->shouldReceive('setPostTerms')->once()->with(1, [], 'category', false);

        $sutInstance = new SetPostTermsFromContent('post', 'category', $tagReader, $wpService);
        $sutInstance->setPostTermsFromContent($post->ID);

        $this->assertConditionsMet();
    }
}
