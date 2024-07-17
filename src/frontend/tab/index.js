import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import metadata from './block.json';
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useEffect } from '@wordpress/element';

registerBlockType(metadata.name, {
  edit: ({ clientId, context, attributes, setAttributes }) => {
    const blockProps = useBlockProps();

    const parentBlockID = select('core/block-editor').getBlockParentsByBlockName(clientId, ['anspress/tabs']);
    const currentTabIndex = useSelect(() => select('core/block-editor').getBlockAttributes(parentBlockID[0]).currentTabIndex);
    const getBlockIndex = select('core/block-editor').getBlockOrder(parentBlockID[0]).indexOf(clientId);
    const hasInnerBlocks = useSelect(() => select('core/block-editor').getBlocks(clientId).length > 0);

    useEffect(() => {
      setAttributes({ tabIndex: getBlockIndex });
    }, [getBlockIndex]);

    return (
      <div {...blockProps}>
        <InspectorControls>
        </InspectorControls>

        {getBlockIndex === currentTabIndex && (
          <>
            <InnerBlocks renderAppender={
              () => !hasInnerBlocks ? <InnerBlocks.ButtonBlockAppender /> : false
            } />
          </>
        )}
      </div>
    );
  },

  save: () => {
    const blockProps = useBlockProps.save();

    return (
      <div {...blockProps}>
        <InnerBlocks.Content />
      </div>
    );
  },
});
