<?php

namespace Tests\Feature;

use App\Alias;
use App\AliasRecipient;
use App\Recipient;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AliasRecipientsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function user_can_attach_recipient_to_alias()
    {
        $alias = factory(Alias::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->json('POST', '/alias-recipients', [
            'alias_id' => $alias->id,
            'recipient_ids' => [$recipient->id]
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, $alias->recipients);
        $this->assertEquals($recipient->email, $alias->recipients[0]->email);
    }

    /** @test */
    public function user_can_attach_multiple_recipients_to_alias()
    {
        $alias = factory(Alias::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient1 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient2 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient3 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->json('POST', '/alias-recipients', [
            'alias_id' => $alias->id,
            'recipient_ids' => [$recipient1->id, $recipient2->id, $recipient3->id]
        ]);

        $response->assertStatus(200);
        $this->assertCount(3, $alias->recipients);
    }

    /** @test */
    public function user_can_update_existing_recipients_for_alias()
    {
        $alias = factory(Alias::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient1 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        AliasRecipient::create([
            'alias' => $alias,
            'recipient' => $recipient1
        ]);

        $recipient2 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipient3 = factory(Recipient::class)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->json('POST', '/alias-recipients', [
            'alias_id' => $alias->id,
            'recipient_ids' => [$recipient2->id, $recipient3->id]
        ]);

        $response->assertStatus(200);
        $this->assertCount(2, $alias->recipients);
    }

    /** @test */
    public function user_cannot_attach_unverified_recipient_to_alias()
    {
        $alias = factory(Alias::class)->create([
            'user_id' => $this->user->id
        ]);

        $unverifiedRecipient = factory(Recipient::class)->create([
            'user_id' => $this->user->id,
            'email_verified_at' => null
        ]);

        $response = $this->json('POST', '/alias-recipients', [
            'alias_id' => $alias->id,
            'recipient_ids' => [$unverifiedRecipient->id]
        ]);

        $response->assertStatus(422);
        $this->assertCount(0, $alias->recipients);
    }

    /** @test */
    public function user_cannot_attach_more_than_allowed_recipients_to_alias()
    {
        $alias = factory(Alias::class)->create([
            'user_id' => $this->user->id
        ]);

        $recipients = factory(Recipient::class, 11)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->json('POST', '/alias-recipients', [
            'alias_id' => $alias->id,
            'recipient_ids' => $recipients->pluck('id')
        ]);

        $response->assertStatus(422);
        $this->assertCount(0, $alias->recipients);
    }
}
