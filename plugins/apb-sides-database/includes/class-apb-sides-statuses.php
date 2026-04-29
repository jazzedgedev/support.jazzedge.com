<?php
/**
 * Central status string constants for APB Sides Database.
 *
 * @package APB_Sides_Database
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Status constants used across uploads, parses, AI, and entities.
 */
class APB_Sides_Statuses {

	public const UPLOADED       = 'uploaded';
	public const PARSED         = 'parsed';
	public const PARSE_FAILED   = 'parse_failed';
	public const OCR_PENDING    = 'ocr_pending';
	public const AI_PENDING     = 'ai_pending';
	public const AI_COMPLETE    = 'ai_complete';
	public const AI_FAILED      = 'ai_failed';
	public const REVIEW_PENDING = 'review_pending';
	public const APPROVED       = 'approved';
	public const PUBLISHED      = 'published';
	public const UNPUBLISHED    = 'unpublished';
	public const REJECTED       = 'rejected';

	/** Review queue row awaiting human decision. */
	public const REVIEW_QUEUE_PENDING = 'pending';

	/** Parsed document not yet processed. */
	public const PARSE_STATUS_PENDING = 'pending';

	/** AI extraction not yet run or incomplete. */
	public const EXTRACTION_STATUS_PENDING = 'pending';
}
