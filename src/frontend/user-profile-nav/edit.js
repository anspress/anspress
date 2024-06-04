import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';

const Edit = ({ attributes }) => {
  const { avatarPosition } = attributes;

  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title="Avatar Settings">
          {/* <SelectControl
            label="Avatar Position"
            value={avatarPosition}
            options={[
              { label: 'Left', value: 'left' },
              { label: 'Right', value: 'right' },
              { label: 'Top', value: 'top' },
              { label: 'Bottom', value: 'bottom' }
            ]}
            onChange={(value) => setAttributes({ avatarPosition: value })}
          />
          <RangeControl
            label="Avatar Size"
            value={avatarSize}
            onChange={(value) => setAttributes({ avatarSize: value })}
            min={24}
            max={192}
          /> */}
        </PanelBody>
      </InspectorControls>
      <div className='wp-block-anspress-question-answer-user-profile-nav-items'>
        <div className='wp-block-anspress-question-answer-user-profile-nav-item active-nav'>
          <a href='#'>Questions</a>
        </div>
        <div className='wp-block-anspress-question-answer-user-profile-nav-item'>
          <a href='#'>Answers</a>
        </div>
        <div className='wp-block-anspress-question-answer-user-profile-nav-item'>
          <a href='#'>Comments</a>
        </div>
        <div className='wp-block-anspress-question-answer-user-profile-nav-item'>
          <a href='#'>Reputations</a>
        </div>
      </div>
    </div>
  );
};

export default Edit;
