import { __ } from '@wordpress/i18n';
import { ExternalLink } from '@wordpress/components';
import { Notice } from '@wordpress/components';

export const ProNoticeComponent = () => {
  return (
    <Notice status="warning" isDismissible={false}>
      <p>{__('In the premium version of the plugin, every aspect of this block can be customized.', 'anspress-question-answer')}</p>
      <ExternalLink href="https://anspress.net/pro">{__('Get the Pro version', 'anspress-question-answer')}</ExternalLink>
    </Notice>
  )
}
