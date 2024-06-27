import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl, Button } from '@wordpress/components';
import { useState } from 'react';

const Edit = ({ attributes, setAttributes }) => {
  const { avatarPosition, avatarSize, displayName, metaFields, showBiography } = attributes;

  const [newMeta, setNewMeta] = useState('');

  const blockProps = useBlockProps();

  const updateMetaFields = (index, value) => {
    const newMetaFields = [...metaFields];
    newMetaFields[index] = value;
    setAttributes({ metaFields: newMetaFields });
  };

  const addMetaField = () => {
    if (newMeta.trim()) {
      setAttributes({ metaFields: [...metaFields, newMeta] });
      setNewMeta('');
    }
  };

  const removeMetaField = (index) => {
    const newMetaFields = metaFields.filter((_, i) => i !== index);
    setAttributes({ metaFields: newMetaFields });
  };

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title="Avatar Settings">
          <SelectControl
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
          />
        </PanelBody>
        <PanelBody title="Display Settings">
          <ToggleControl
            label="Display Name"
            checked={displayName}
            onChange={(value) => setAttributes({ displayName: value })}
          />
          <TextControl
            label="Add Custom Meta Field"
            value={newMeta}
            onChange={(value) => setNewMeta(value)}
            onKeyPress={(e) => e.key === 'Enter' && addMetaField()}
          />
          <Button isPrimary onClick={addMetaField} style={{ marginTop: '10px' }}>Add Meta Field</Button>
          <div>
            {metaFields.map((field, index) => (
              <div key={index} style={{ marginTop: '10px' }}>
                <TextControl
                  value={field}
                  onChange={(value) => updateMetaFields(index, value)}
                />
                <Button isDestructive onClick={() => removeMetaField(index)} style={{ marginLeft: '10px' }}>Remove</Button>
              </div>
            ))}
          </div>
        </PanelBody>
      </InspectorControls>
      <div className={`user-profile-block avatar-${avatarPosition}`}>
        <img
          src="https://via.placeholder.com/150"
          alt="User Avatar"
          style={{ width: avatarSize, height: avatarSize }}
        />
        <div className="user-info">
          {displayName && <div className="user-name">User Name</div>}

          {showBiography && <div className="user-biography">User Biography</div>}

          {metaFields.map((field, index) => (
            <p key={index} className="user-meta">{field}: Example Data</p>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Edit;
