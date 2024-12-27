<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

// Create a shortcut for params.
$params  = &$this->item->params;
$images  = json_decode($this->item->images ?? "");
$canEdit = $this->item->params->get('access-edit');
$info    = $this->item->params->get('info_block_position', 0);
$article_format = (isset($attribs->helix_ultimate_article_format) && $attribs->helix_ultimate_article_format) ? $attribs->helix_ultimate_article_format : 'standard';

// Check if associations are implemented. If they are, define the parameter.
$assocParam = (Associations::isEnabled() && $params->get('show_associations'));

$currentDate   = Factory::getDate()->format('Y-m-d H:i:s');
$isNotPublishedYet = $this->item->publish_up > $currentDate;
$isUnpublished = JVERSION < 4 ? ($this->item->state == 0 || strtotime($this->item->publish_up) > strtotime(Factory::getDate()) || ((strtotime($this->item->publish_down) < strtotime(Factory::getDate())) && $this->item->publish_down != Factory::getDbo()->getNullDate())) : ($this->item->state == Joomla\Component\Content\Administrator\Extension\ContentComponent::CONDITION_UNPUBLISHED || $isNotPublishedYet)
	|| ($this->item->publish_down < $currentDate && $this->item->publish_down !== null);
$isExpired         = JVERSION < 4 ? $this->item->publish_down < $currentDate && $this->item->publish_down !== Factory::getDbo()->getNullDate() : !is_null($this->item->publish_down) && $this->item->publish_down < $currentDate;

$version = new Version();
$JoomlaVersion = $version->getShortVersion();

$template = HelixUltimate\Framework\Platform\Helper::loadTemplateData();
$tmpl_params = $template->params;

$content_center = $tmpl_params->get('blog_center_content');

$content_margin = $tmpl_params->get('leading_blog_list_content_margin', 'default');
$content_margin_cls = $content_margin == 'default' ? 'uk-margin-top' : 'uk-margin-'.$content_margin.'-top' ;
$content_margin_cls .= $content_center ? ' uk-text-center' : '';

$title_style = $tmpl_params->get('leading_blog_list_title', 'h2');
$title_style_cls = $title_style == 'none' ? ' uk-article-title' : ' uk-'.$title_style;

$title_margin = $tmpl_params->get('leading_blog_list_title_margin', 'default');
$title_margin_cls = $title_margin == 'default' ? 'uk-margin-top' : 'uk-margin-'.$title_margin.'-top' ;
$title_margin_cls .= $content_center ? ' uk-flex-center' : '';

$content_length = $tmpl_params->get('content_length');
$image_margin = $tmpl_params->get('image_margin', 'default');
$image_margin_cls = $image_margin == 'default' ? ' uk-margin-top' : ' uk-margin-'.$image_margin.'-top';
$blog_tag_cls = $content_center ? ' class="uk-text-center"' : '';

?>

<?php if($article_format == 'gallery') : ?>
	<?php echo LayoutHelper::render('joomla.content.blog.gallery', array('attribs' => $attribs, 'id'=>$this->item->id)); ?>
<?php elseif($article_format == 'video') : ?>
	<?php echo LayoutHelper::render('joomla.content.blog.video', array('attribs' => $attribs)); ?>
<?php elseif($article_format == 'audio') : ?>
	<?php echo LayoutHelper::render('joomla.content.blog.audio', array('attribs' => $attribs)); ?>
<?php else: ?>
	<?php echo LayoutHelper::render('joomla.content.intro_image', $this->item); ?>
<?php endif; ?>

<?php if ($isUnpublished) : ?>
	<div class="system-unpublished">
<?php endif; ?>

<?php if ($info == 0) : ?>
	<?php echo LayoutHelper::render('joomla.content.meta_block', $this->item); ?>
<?php endif; ?>

<?php echo LayoutHelper::render('joomla.content.blog_style_default_item_title', $this->item); ?>

<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
<?php echo $this->item->event->afterDisplayTitle; ?>

<?php if ($assocParam && !empty($this->item->associations)) : ?>
	<div class="cat-list-association uk-margin-small-top">
	<?php if (JVERSION < 4): ?>
		<?php $associations = ContentHelperAssociation::displayAssociations($this->item->id); ?>
	<?php else: ?>
		<?php $associations = Joomla\Component\Content\Site\Helper\AssociationHelper::displayAssociations($this->item->id); ?>
	<?php endif; ?>
	<span class="icon-globe icon-fw" aria-hidden="true"></span>
	<?php echo Text::_('JASSOCIATIONS'); ?>
	<?php foreach ($associations as $association) : ?>
		<?php if ($this->params->get('flags', 1) && $association['language']->image) : ?>
			<?php $flag = HTMLHelper::_('image', 'mod_languages/' . $association['language']->image . '.gif', $association['language']->title_native, ['title' => $association['language']->title_native], true); ?>
			<a href="<?php echo Route::_($association['item']); ?>"><?php echo $flag; ?></a>
		<?php else : ?>
			<?php $class = 'btn btn-secondary btn-sm btn-' . strtolower($association['language']->lang_code); ?>
			<a class="<?php echo $class; ?>" title="<?php echo $association['language']->title_native; ?>" href="<?php echo Route::_($association['item']); ?>"><?php echo $association['language']->lang_code; ?>
				<span class="visually-hidden"><?php echo $association['language']->title_native; ?></span>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ($info != 0) : ?>
	<?php echo LayoutHelper::render('joomla.content.meta_block', $this->item); ?>
<?php endif; ?>

<?php if (isset($images->image_intro) && !empty($images->image_intro) && $params->get('float_intro') != 'none') : ?>
	<div class="uk-text-center<?php echo $image_margin_cls; ?>">
		<?php echo LayoutHelper::render('joomla.content.intro_image', $this->item); ?>
	</div>
<?php endif; ?>

<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
<?php echo $this->item->event->beforeDisplayContent; ?>

<div class="<?php echo $content_margin_cls; ?>" property="text">
	<?php if (is_numeric($content_length) && $content_length >= 0) : ?>
		<?php echo substr(strip_tags($this->item->introtext), 0, $content_length) . '...'; ?>
	<?php else : ?>
		<?php echo $this->item->introtext; ?>
	<?php endif ?> 
</div>

<?php if ($info == 0 && $params->get('show_tags', 1) && !empty($this->item->tags->itemTags)) : ?>
	<p<?php echo $blog_tag_cls; ?>><?php echo LayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?></p>
<?php endif; ?>

<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = Route::_(version_compare($JoomlaVersion, '4.0.0', '>=') ? Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language) : ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
	else :
		$menu = Factory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link = new Uri(Route::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
		$link->setVar('return', base64_encode(version_compare($JoomlaVersion, '4.0.0', '>=') ? Joomla\Component\Content\Site\Helper\RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language) : ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
	endif; ?>

	<?php echo LayoutHelper::render('joomla.content.readmore', ['item' => $this->item, 'params' => $params, 'link' => $link]); ?>
<?php endif; ?>

<?php if ($isUnpublished) : ?>
	</div>
<?php endif; ?>

<?php if ($params->get('show_create_date') || $params->get('show_modify_date') || $params->get('show_hits')) : ?>
	<ul class="uk-list">
		<?php if ($params->get('show_create_date')) : ?>
			<li>
				<time datetime="<?php echo HTMLHelper::_('date', $this->item->created, 'c'); ?>" itemprop="dateCreated">
					<?php echo Text::sprintf('TPL_META_DATE_CREATED', HTMLHelper::_('date', $this->item->created, Text::_('DATE_FORMAT_LC3'))); ?>
				</time>
			</li>
		<?php endif ?>

		<?php if ($params->get('show_modify_date')) : ?>
			<li>
				<time datetime="<?php echo HTMLHelper::_('date', $this->item->modified, 'c'); ?>" itemprop="dateModified">
					<?php echo Text::sprintf('TPL_META_DATE_MODIFIED', HTMLHelper::_('date', $this->item->modified, Text::_('DATE_FORMAT_LC3'))); ?>
				</time>
			</li>
		<?php endif ?>

		<?php if ($params->get('show_hits')) : ?>
			<li>
				<meta content="UserPageVisits:<?php echo $this->item->hits; ?>" itemprop="interactionCount">
				<?php echo Text::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?>
			</li>
		<?php endif ?>
	</ul>
<?php endif ?>

<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
	<?php echo LayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>
<?php endif; ?>

<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
<?php echo $this->item->event->afterDisplayContent; ?>