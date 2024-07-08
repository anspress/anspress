import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';
import { useEffect } from 'react';
import ServerSideRender from '@wordpress/server-side-render';

const Edit = ({ attributes, setAttributes }) => {
  const { itemsPerPage, showPagination, showCount, showDescription, descriptionLength, columns, showIcon, showImage } = attributes;
  console.log(attributes)

  const { terms, hasResolved } = useSelect(
    (select) => {
      const selectArgs = ['taxonomy', 'question_category', { per_page: itemsPerPage }];
      return {
        terms: select(coreDataStore).getEntityRecords(...selectArgs),
        hasResolved: select(coreDataStore).hasFinishedResolution('getEntityRecords', selectArgs),
      };
    },
    [itemsPerPage]
  );

  useEffect(() => {
    if (!columns) {
      setAttributes({ columns: 1 });
    }
  }, [columns]);

  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody title="Settings">
          <RangeControl
            label="Items Per Page"
            value={itemsPerPage}
            onChange={(value) => setAttributes({ itemsPerPage: value })}
            min={1}
            max={30}
          />

          <ToggleControl
            label="Show image"
            checked={showImage}
            onChange={(value) => setAttributes({ showImage: value })}
            help="Show the image of the category"
          />

          <ToggleControl
            label="Show description"
            checked={showDescription}
            onChange={(value) => setAttributes({ showDescription: value })}
            help="Show the description of the category"
          />

          <RangeControl
            label="Description length"
            value={descriptionLength}
            onChange={(value) => setAttributes({ descriptionLength: value })}
            min={2}
            max={100}
            help="Number of words to show in the description"
          />

          <ToggleControl
            label="Show count"
            checked={showCount}
            onChange={(value) => setAttributes({ showCount: value })}
            help="Show the count of the questions in the category"
          />

          <RangeControl
            label="Columns"
            value={columns}
            onChange={(value) => setAttributes({ columns: value })}
            min={1}
            max={4}
          />
          <ToggleControl
            label="Show Pagination"
            checked={showPagination}
            onChange={(value) => setAttributes({ showPagination: value })}
          />
        </PanelBody>
      </InspectorControls>

      <ServerSideRender
        block="anspress/categories"
        attributes={attributes}
      />
    </div>
  );
};

export default Edit;
