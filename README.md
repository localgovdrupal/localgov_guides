# LocalGovDrupal Guides

Provides paginated navigation through pages with an overview page and
block. A part of the LocalGovDrupal distribution, that can also be installed
stand-alone.

## Install process:

Standard Drupal module installation process applies. But...

The 2 x block configuration files are only installed if you are using the LocalGov Drupal base theme or Scarfolk theme. 
So before installing this module, copy these two files 

config/optional/block.block.localgov_guides_contents_base.yml
config/optional/block.block.localgov_guides_prev_next_block_base.yml

and make the following changes to the copies of the files:

- name the copies of the files consistently for your theme, e.g. localgov_guides_contents_mytheme.yml
- update the id parameters, .e.g id: localgov_guides_contents_mytheme
- replace "localgov_base" with your theme name, update the id  and set a region available to the theme:

Note: if you have a long machine name for your theme, consider shortening the naming used here to avoid running into errors.

You can revert these changes after module installation as these files are no longer needed.

Alternatively, add these two blocks from the Drupal block layout admin page.

## Content types:

 * Overview - the top level section for each guide;
 * Page - the page that can be placed in a guide.

Additional optional integration into LocalGovDrupal Services sections,
and topics.
