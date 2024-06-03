import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
  const { showPagination } = attributes;
  const blockProps = useBlockProps.save();

  return (
    <div {...blockProps}>
      <div>
        <InnerBlocks.Content />
      </div>
      {showPagination && (
        <div className="anspress-pagination">
          <InnerBlocks.Content />
        </div>
      )}
    </div>
  );
};

export default Save;
