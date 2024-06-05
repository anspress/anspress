import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';
import ServerSideRender from '@wordpress/server-side-render';

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

      <ServerSideRender
        block="anspress/user-profile-nav"
        attributes={attributes}
      />
    </div>
  );
};

export default Edit;
