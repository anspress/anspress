import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
  const { avatarPosition, avatarSize, displayName, metaFields } = attributes;
  const blockProps = useBlockProps.save();

  return (
    <div {...blockProps} className={`user-profile-block avatar-${avatarPosition}`}>
      <img
        src="https://via.placeholder.com/150"
        alt="User Avatar"
        style={{ width: avatarSize, height: avatarSize }}
      />
      <div className="user-info">
        {displayName && <p className="user-name">User Name</p>}
        {metaFields.map((field, index) => (
          <p key={index} className="user-meta">{field}: Example Data</p>
        ))}
      </div>
    </div>
  );
};

export default Save;
