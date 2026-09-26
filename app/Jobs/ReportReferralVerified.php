<?php

namespace App\Jobs;

/**
 * Replaced by ReportCustomerVerified (POST customers/{id}/verified for every player). Kept only so
 * jobs queued before the deploy still run; dispatch ReportCustomerVerified instead. Remove once
 * the queues have drained.
 *
 * @deprecated
 */
class ReportReferralVerified extends ReportCustomerVerified {}
