import { useBlockProps, InspectorControls, InnerBlocks, store, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useEffect } from 'react';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { ProNoticeComponent } from '../common/js/ProNoticeComponent';
import { InspectorProvider } from '../common/InspectorProvider';

const Edit = ({ attributes, setAttributes, clientId }) => {
    const innerBlocks = useSelect(
        (select) => select(store).getBlock(clientId).innerBlocks,
        [clientId]
    );

    const { activeTab, questionsAttributes, reputationsAttributes } = attributes;

    const blockProps = useBlockProps();
    const innerBlocksProps = useInnerBlocksProps(blockProps, {
        allowedBlocks: ['anspress/questions'],
        renderAppender: false,
    });

    useEffect(() => {
        if (innerBlocks.length) {
            const newQuestionsAttributes = innerBlocks.find(block => block.name === 'anspress/questions')?.attributes;
            const newReputationsAttributes = innerBlocks.find(block => block.name === 'anspress/reputations')?.attributes;

            if (newQuestionsAttributes !== questionsAttributes || newReputationsAttributes !== reputationsAttributes) {
                setAttributes({ questionsAttributes: newQuestionsAttributes, reputationsAttributes: newReputationsAttributes });
            }
        }
    }, [innerBlocks]);

    const tabs = [
        { key: "questions", title: 'Questions', link: '#' },
        { key: "answers", title: 'Answers', link: '#' },
        { key: "reputations", title: 'Reputations', link: '#' },
    ];

    const tabClickHandler = (e, tab) => {
        e.preventDefault();
        setAttributes({ activeTab: tab.key });
    }

    return (
        <div {...blockProps}>
            <InspectorControls>
                <ProNoticeComponent />
                <PanelBody title="User Profile Nav Settings">
                    <SelectControl
                        label="Active Tab"
                        value={activeTab}
                        options={[
                            { label: 'Questions', value: 'questions' },
                            { label: 'Answers', value: 'answers' },
                            { label: 'Reputations', value: 'reputations' },
                        ]}
                        onChange={(value) => setAttributes({ activeTab: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div className='wp-block-anspress-user-profile-nav-items'>
                {tabs.map((item) => (
                    <div key={item.key} className={`wp-block-anspress-user-profile-nav-item${activeTab === item.key ? ' active-nav' : ''}`}>
                        <a href={item.link} onClick={e => tabClickHandler(e, item)}>{item.title}</a>
                    </div>
                ))}
            </div>
            {activeTab === 'questions' && <div {...innerBlocksProps}> <ServerSideRender block="anspress/questions" attributes={questionsAttributes} /></div>}
            {activeTab === 'reputations' && <ServerSideRender block="anspress/reputations" attributes={reputationsAttributes} />}
        </div>
    );
};

export default Edit;
