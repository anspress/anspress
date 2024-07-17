import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import './style.scss';

registerBlockType('anspress/tabs', {
  edit: Edit,
  save,
});
