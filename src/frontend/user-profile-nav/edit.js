import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ({ attributes }) => {
  const { avatarPosition } = attributes;

  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <ServerSideRender
        block="anspress/user-profile-nav"
        attributes={attributes}
      />
    </div>
  );
};

export default Edit;
