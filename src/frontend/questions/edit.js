import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, RangeControl, SelectControl, SearchControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { useState, useEffect } from 'react';
import { useSelect } from '@wordpress/data';
import { TextControl } from '@wordpress/components';

const Edit = ({ attributes, setAttributes }) => {
  const {
    query,
    displayAvatar,
    displayVoteCount,
    displayAnsCount,
    displayActivity,
    displayViews,
    displaySolved,
    displayCategories,
    displayTags,
    currentAuthor,
    displayPagination,
    itemsPerPage
  } = attributes;

  const blockProps = useBlockProps();

  const [categorySearch, setCategorySearch] = useState('');
  const [tagSearch, setTagSearch] = useState('');
  const [authorSearch, setAuthorSearch] = useState('');

  const categories = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'question_category', { search: categorySearch, per_page: 10 }),
    [categorySearch]
  );

  const tags = useSelect(
    (select) => select('core').getEntityRecords('taxonomy', 'question_tag', { search: tagSearch, per_page: 10 }),
    [tagSearch]
  );

  const authors = useSelect(
    (select) => select('core').getEntityRecords('root', 'user', { search: authorSearch, per_page: 10 }),
    [authorSearch]
  );

  const categoryOptions = categories ? categories.map((category) => ({
    label: category.name,
    value: category.id,
  })) : [];

  const tagOptions = tags ? tags.map((tag) => ({
    label: tag.name,
    value: tag.id,
  })) : [];

  const authorOptions = authors ? authors.map((author) => ({
    label: author.name,
    value: author.id,
  })) : [];

  return (
    <div {...blockProps}>
      <InspectorControls>

        <PanelBody title={__('Query Settings', 'anspress-question-answer')}>
          <SelectControl
            label={__('Order By', 'anspress-question-answer')}
            value={query.orderBy}
            options={[
              { label: __('Date', 'anspress-question-answer'), value: 'date' },
              { label: __('Title', 'anspress-question-answer'), value: 'title' },
              { label: __('Author', 'anspress-question-answer'), value: 'author' },
              { label: __('Random', 'anspress-question-answer'), value: 'rand' },
            ]}
            onChange={(value) => setAttributes({ query: { ...query, orderBy: value } })}
          />
          <SelectControl
            label={__('Order', 'anspress-question-answer')}
            value={query.order}
            options={[
              { label: __('Descending', 'anspress-question-answer'), value: 'desc' },
              { label: __('Ascending', 'anspress-question-answer'), value: 'asc' },
            ]}
            onChange={(value) => setAttributes({ query: { ...query, order: value } })}
          />
        </PanelBody>

        <PanelBody title={__('Tags Settings', 'anspress-question-answer')}>
          <SearchControl
            label={__('Search Tags', 'anspress-question-answer')}
            help={__('Search for tags', 'anspress-question-answer')}
            value={tagSearch}
            onChange={(value) => setTagSearch(value)}
          />

          <SelectControl
            label={__('Tags', 'anspress-question-answer')}
            value={query.tags}
            options={tagOptions}
            onChange={(value) => setAttributes({ query: { ...query, tags: value } })}
            multiple
          />
        </PanelBody>

        <PanelBody title={__('Category Settings', 'anspress-question-answer')}>
          <SearchControl
            label={__('Search Categories', 'anspress-question-answer')}
            value={categorySearch}
            onChange={(value) => setCategorySearch(value)}
          />
          <SelectControl
            label={__('Categories', 'anspress-question-answer')}
            value={query.categories}
            options={categoryOptions}
            onChange={(value) => setAttributes({ query: { ...query, categories: value } })}
            multiple
          />
        </PanelBody>

        <PanelBody title={__('Author Settings', 'anspress-question-answer')}>
          <ToggleControl
            label={__('Use current author', 'anspress-question-answer')}
            help={__('Display questions of current author', 'anspress-question-answer')}
            checked={currentAuthor}
            onChange={(value) => setAttributes({ currentAuthor: value })}
          />

          {!currentAuthor && (
            <div>
              <SearchControl
                label={__('Search Authors', 'anspress-question-answer')}
                help={__('Search for authors', 'anspress-question-answer')}
                value={authorSearch}
                onChange={(value) => setAuthorSearch(value)}
              />

              <SelectControl
                value={query.authors}
                options={authorOptions}
                onChange={(value) => setAttributes({ query: { ...query, authors: value } })}
                multiple
              />
            </div>
          )}
        </PanelBody>

        <PanelBody title={__('Pagination Settings', 'anspress-question-answer')}>
          <ToggleControl
            label={__('Display Pagination', 'anspress-question-answer')}
            checked={displayPagination}
            onChange={(value) => setAttributes({ displayPagination: value })}
          />

          {displayPagination && (
            <TextControl
              label={__('Items per page', 'anspress-question-answer')}
              value={itemsPerPage}
              onChange={(value) => setAttributes({ itemsPerPage: value })}
            />
          )}
        </PanelBody>

        <PanelBody title={__('Display Settings', 'anspress-question-answer')}>
          <ToggleControl
            label={__('Display Avatar', 'anspress-question-answer')}
            checked={displayAvatar}
            onChange={(value) => setAttributes({ displayAvatar: value })}
          />
          <ToggleControl
            label={__('Display Votes Count', 'anspress-question-answer')}
            checked={displayVoteCount}
            onChange={(value) => setAttributes({ displayVoteCount: value })}
          />
          <ToggleControl
            label={__('Display Answer Count', 'anspress-question-answer')}
            checked={displayAnsCount}
            onChange={(value) => setAttributes({ displayAnsCount: value })}
          />
          <ToggleControl
            label={__('Display Views', 'anspress-question-answer')}
            checked={displayViews}
            onChange={(value) => setAttributes({ displayViews: value })}
          />
          <ToggleControl
            label={__('Display Activity', 'anspress-question-answer')}
            checked={displayActivity}
            onChange={(value) => setAttributes({ displayActivity: value })}
          />
          <ToggleControl
            label={__('Display Solved', 'anspress-question-answer')}
            checked={displaySolved}
            onChange={(value) => setAttributes({ displaySolved: value })}
          />
          <ToggleControl
            label={__('Display Categories', 'anspress-question-answer')}
            checked={displayCategories}
            onChange={(value) => setAttributes({ displayCategories: value })}
          />
          <ToggleControl
            label={__('Display Tags', 'anspress-question-answer')}
            checked={displayTags}
            onChange={(value) => setAttributes({ displayTags: value })}
          />
        </PanelBody>
      </InspectorControls>
      <ServerSideRender
        block="anspress/questions"
        attributes={attributes}
      />
    </div>
  );
};

export default Edit;
