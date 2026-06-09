<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $feature
 * @property string $input_hash
 * @property string $output
 * @property string $model
 * @property array<array-key, mixed>|null $sources
 * @property string|null $reviewer_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereFeature($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereInputHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereOutput($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereSources($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AILog whereUpdatedAt($value)
 */
	class AILog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $category
 * @property string|null $badge
 * @property string|null $excerpt
 * @property string|null $content
 * @property string|null $cover_image
 * @property \App\Models\User|null $author
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property bool $is_paid
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ticker> $tickers
 * @property-read int|null $tickers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereBadge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCoverImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereIsPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
 */
	class Article extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\KIBrief|null $kiBrief
 * @property-read \App\Models\Ticker|null $ticker
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disclosure newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disclosure newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Disclosure query()
 */
	class Disclosure extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Disclosure|null $disclosure
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KIBrief newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KIBrief newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KIBrief query()
 */
	class KIBrief extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $meta_description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 */
	class Page extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $user_id
 * @property string|null $certification
 * @property array<array-key, mixed>|null $specializations
 * @property string|null $portfolio_link
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property bool $is_verified
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereBankAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereBankAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCertification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner wherePortfolioLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereSpecializations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereUserId($value)
 */
	class Partner extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $user_id
 * @property string $paket
 * @property int $durasi_hari
 * @property int $nominal
 * @property string|null $bukti_url
 * @property string|null $bukti_path
 * @property string $status
 * @property string|null $submitted_at
 * @property string|null $verified_at
 * @property string|null $verified_by
 * @property string|null $admin_notes
 * @property string|null $user_email
 * @property string|null $user_nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereBuktiPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereBuktiUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereDurasiHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission wherePaket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereUserEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereUserNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentSubmission whereVerifiedBy($value)
 */
	class PaymentSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $category_id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $image
 * @property string $status
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @property-read string|null $image_url
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 */
	class Post extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $ticker
 * @property string|null $sector
 * @property string|null $location
 * @property string $status
 * @property string|null $price
 * @property string|null $image
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTicker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Property whereUpdatedAt($value)
 */
	class Property extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $ticker
 * @property string|null $sector
 * @property string|null $subtitle
 * @property string|null $revenue
 * @property string|null $patmi
 * @property string|null $sales
 * @property string|null $tags
 * @property string|null $date
 * @property string|null $price
 * @property string|null $content
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research wherePatmi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereSubtitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereTicker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Research whereUpdatedAt($value)
 */
	class Research extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $project_id
 * @property string $file_name
 * @property string $file_path
 * @property string|null $extracted_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ResearchProject $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereExtractedText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDocument whereUpdatedAt($value)
 */
	class ResearchDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $project_id
 * @property string|null $model_used
 * @property array<array-key, mixed>|null $structured_json
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ResearchProject $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereModelUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereStructuredJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchDraft whereUpdatedAt($value)
 */
	class ResearchDraft extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $ticker
 * @property string $title
 * @property string|null $prompt
 * @property string $status
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResearchDocument> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ResearchDraft> $drafts
 * @property-read int|null $drafts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject wherePrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereTicker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResearchProject whereUpdatedAt($value)
 */
	class ResearchProject extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $page_id
 * @property string $key
 * @property string|null $title
 * @property array<array-key, mixed>|null $content
 * @property int $order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Page $page
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereUpdatedAt($value)
 */
	class Section extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $symbol
 * @property string $company_name
 * @property string|null $sector
 * @property string|null $description
 * @property numeric|null $current_price
 * @property numeric|null $target_price
 * @property string|null $recommendation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Disclosure> $disclosures
 * @property-read int|null $disclosures_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereTargetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ticker whereUpdatedAt($value)
 */
	class Ticker extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property int $is_migrated
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Partner|null $partner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Research> $researches
 * @property-read int|null $researches_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentSubmission> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $teams
 * @property-read int|null $teams_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Watchlist> $watchlists
 * @property-read int|null $watchlists_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsMigrated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $user_id
 * @property int $ticker_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ticker $ticker
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereTickerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Watchlist whereUserId($value)
 */
	class Watchlist extends \Eloquent {}
}

