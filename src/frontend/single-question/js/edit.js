import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, SearchControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';
import ServerSideRender from '@wordpress/server-side-render';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { ExternalLink } from '@wordpress/components';

const Edit = ({ attributes, setAttributes }) => {
  const { currentQuestionId } = attributes;

  const blockProps = useBlockProps();

  const [questionSearch, setQuestionSearch] = useState('');

  const questions = useSelect(
    (select) => select('core').getEntityRecords('postType', 'question', { search: questionSearch, per_page: 10 }),
    [questionSearch]
  );

  const questionsOptions = questions ? questions.map((q) => ({
    label: q.title.rendered,
    value: q.id,
  })) : [];

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title={__('Preview Settings', 'anspress-question-answer')}>
          <p>{__('Select a question from the options below or search for a specific question to display it in the editor.', 'anspress-question-answer')}</p>

          <SearchControl
            label={__('Search for a Question', 'anspress-question-answer')}
            help={__('Search for questions', 'anspress-question-answer')}
            value={questionSearch}
            onChange={(value) => setQuestionSearch(value)}
          />

          <SelectControl
            label={__('Questions', 'anspress-question-answer')}
            value={currentQuestionId}
            options={questionsOptions}
            onChange={(value) => setAttributes({ currentQuestionId: value })}
          />
        </PanelBody>
        <PanelBody title={__('Customization', 'anspress-question-answer')}>
          <p>{__('In the premium version of the plugin, every aspect of this block can be customized.', 'anspress-question-answer')}</p>
          <ExternalLink href="https://anspress.net/pro">{__('Get the Pro version', 'anspress-question-answer')}</ExternalLink>

          <br />
          <br />

          <RangeControl
            label={__('Avatar Size', 'anspress-question-answer')}
            value={attributes.avatarSize}
            min={20}
            max={120}
            onChange={(value) => setAttributes({ avatarSize: value })}
          />

          <RangeControl
            label={__('Comment Avatar Size', 'anspress-question-answer')}
            value={attributes.commentAvatarSize}
            min={20}
            max={120}
            onChange={(value) => setAttributes({ commentAvatarSize: value })}
          />
        </PanelBody>
      </InspectorControls>

      <ServerSideRender block="anspress/single-question" attributes={attributes} />
    </div>
  );
};

export default Edit;
