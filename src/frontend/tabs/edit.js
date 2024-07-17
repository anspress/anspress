import { __ } from '@wordpress/i18n';
import { InnerBlocks, InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { select, useSelect } from '@wordpress/data';
import { ToggleControl, PanelBody, ButtonGroup, Button, TextControl, IconButton, Dashicon, SelectControl } from '@wordpress/components';
import { ProNoticeComponent } from '../common/js/ProNoticeComponent';

const ALLOWED_BLOCKS = ['anspress/tab'];

import './editor.scss';

export default function Edit(props) {

  const {
    attributes,
    setAttributes,
    clientId
  } = props;

  const { tabs = [], defaultTabKey, currentTabIndex } = attributes;

  const hasInnerBlocks = useSelect(() => select('core/block-editor').getBlocks(clientId).length > 0);

  const updateTab = (index, key, value) => {
    const newTabs = [...tabs];
    newTabs[index][key] = value;
    setAttributes({ tabs: newTabs });

    const parentBlockID = props.clientId;
    const blocks = wp.data.select('core/block-editor').getBlocks(parentBlockID);
    const blockToUpdate = blocks[index];
    wp.data.dispatch('core/block-editor').updateBlockAttributes(blockToUpdate.clientId, { [key]: value });
  };

  const moveTab = (index, direction) => {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= tabs.length) return;

    const newTabs = [...tabs];
    const temp = newTabs[index];
    newTabs[index] = newTabs[newIndex];
    newTabs[newIndex] = temp;
    setAttributes({ tabs: newTabs });

    const parentBlockID = props.clientId;
    const blocks = wp.data.select('core/block-editor').getBlocks(parentBlockID);
    const blockToMove = blocks[index];
    wp.data.dispatch('core/block-editor').moveBlockToPosition(blockToMove.clientId, parentBlockID, parentBlockID, newIndex);
  };

  const addNewTab = () => {
    const newTabs = [...tabs, { label: __('New Tab', 'anspress-question-answer'), key: '' }];
    setAttributes({ tabs: newTabs });

    const parentBlockID = props.clientId;
    const innerBlocks = wp.data.select('core/block-editor').getBlocks(clientId);
    const newBlock = wp.blocks.createBlock('anspress/tab', { tabLabel: __('New Tab', 'anspress-question-answer'), tabKey: '' });
    wp.data.dispatch('core/block-editor').insertBlock(newBlock, innerBlocks.length, parentBlockID);

    setAttributes({ currentTabIndex: tabs.length });
  };

  return (
    <div {...useBlockProps()}>
      <InspectorControls>
        <ProNoticeComponent />

        <PanelBody title={__('Tabs', 'anspress-question-answer')}>
          {tabs.map((tab, index) => (
            <div key={index} style={{ marginBottom: '8px', display: 'flex' }}>
              <div>
                <div>
                  <TextControl
                    placeholder={__('Tab Label', 'anspress-question-answer')}
                    value={tab.label}
                    onChange={(value) => updateTab(index, 'label', value)}
                    style={{ marginBottom: '0' }}
                  />
                </div>
                <TextControl
                  placeholder={__('Tab Key', 'anspress-question-answer')}
                  value={tab.key}
                  onChange={(value) => updateTab(index, 'key', value)}
                />
              </div>
              <div>
                <Button
                  icon={<Dashicon icon="arrow-up-alt2" />}
                  label={__('Move Up', 'anspress-question-answer')}
                  onClick={() => moveTab(index, -1)}
                  disabled={index === 0}
                  style={{ height: '20px', width: '20px', display: 'block' }}
                />
                <Button
                  icon={<Dashicon icon="arrow-down-alt2" />}
                  label={__('Move Down', 'anspress-question-answer')}
                  onClick={() => moveTab(index, 1)}
                  disabled={index === tabs.length - 1}
                  style={{ height: '20px', width: '20px' }}
                />
              </div>

            </div>
          ))}
          <SelectControl
            label={__('Default Tab', 'anspress-question-answer')}
            value={defaultTabKey}
            options={tabs.map((tab) => ({ label: tab.label || __('Unnamed Tab', 'anspress-question-answer'), value: tab.key }))} onChange={(value) => setAttributes({ defaultTabKey: value })}
          />

          <Button isPrimary onClick={addNewTab}>
            {__('Add New Tab', 'anspress-question-answer')}
          </Button>
        </PanelBody>
      </InspectorControls>

      <ButtonGroup>
        {tabs.map((tab, index) => (
          <Button
            key={index} isPressed={currentTabIndex === index}
            onClick={() => setAttributes({ currentTabIndex: index })}
          >{tab.label || __('Unnamed Tab', 'anspress-question-answer')}</Button>
        ))}
      </ButtonGroup>

      <InnerBlocks
        allowedBlocks={ALLOWED_BLOCKS}
        renderAppender={() => !hasInnerBlocks ? <Button isPrimary onClick={addNewTab}>{__('Add First Tab', 'anspress-question-answer')}</Button> : false}
      />
    </div>
  );
}
