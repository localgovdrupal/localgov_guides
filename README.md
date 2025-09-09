# LocalGovDrupal Guides

Provides paginated navigation through pages with an overview page and
block. A part of the LocalGovDrupal distribution, that can also be installed
stand-alone.

## Install process:

Standard Drupal module installation process applies. But...

The 2 x block configuration files are only installed if you are using the LocalGov Drupal base theme or Scarfolk theme. 
So before installing this module, copy or edit these two files and replace "localgov_theme" with your theme name and set a region available to the theme:

config/optional/block.block.localgov_guides_contents_base.yml
config/optional/block.block.localgov_guides_prev_next_block_base.yml

You can revert these changes after module installation as these files are no longer needed.

Alternatively, add these two blocks from the Drupal block layout admin page.

## Content types:

 * Overview - the top level section for each guide;
 * Page - the page that can be placed in a guide.

Additional optional integration into LocalGovDrupal Services sections,
and topics.
