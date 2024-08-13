<?php

namespace Tests\Unit\Actions\Payments;

use App\Actions\Payments\GenerateTransferWiseExport;
use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use App\Services\Payments\TransferWiseClient;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class GenerateTransferWiseExportTest extends TestCase
{
    public function testHandle(): void
    {
        $testAmount = 50.0;
        $this->partialMock(TransferWiseClient::class, function (MockInterface $mock) use ($testAmount) {
            $mock->shouldReceive('convert')->andReturn($testAmount);
        });

        $this->travelTo(Carbon::parse('2022-01-15'));
        $team = Team::factory()->create();
        $payments = Payment::factory(3)->create(['team_id' => $team->id, 'target_currency' => 'USD', 'target_amount' => 10]);
        $this->travelBack();

        $startDate = Carbon::parse('2022-01-01');
        $endDate = Carbon::parse('2022-01-31');
        $rows = app(GenerateTransferWiseExport::class)->handle($startDate, $endDate);
        $headings = [
            'recipientId', 'name', 'account', 'sourceCurrency', 'targetCurrency', 'amountCurrency', 'amount',
            'paymentReference', 'addressCountryCode', 'addressCity', 'addressFirstLine', 'addressState',
            'addressPostCode',
        ];
        foreach ($headings as $key => $heading) {
            $this->assertSame($heading, $rows[0][$key]);
        }

        $this->assertSame($team->account_name, $rows[1][1]);
        $this->assertSame($team->iban, $rows[1][2]);
        $this->assertSame('GBP', $rows[1][3]);
        $this->assertSame($team->account_currency, $rows[1][4]);
        $this->assertSame($testAmount, $rows[1][6]);
    }
}
