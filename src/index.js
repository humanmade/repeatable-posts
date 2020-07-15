import { registerPlugin } from "@wordpress/plugins";
import { PluginPostStatusInfo } from "@wordpress/edit-post";
import { Button, Dropdown } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from "@wordpress/i18n";


const HMPostRepeatOptions = {
	no: {
		value: "",
		label: "No"
		description: "",
		onSelect:
		checked:
		onMetaFieldChange: value;
	},
	daily: {
		value: "daily",
		label: "Daily"
		description: "",
		onSelect:
		checked:
		onMetaFieldChange: value;
	},
	weekly: {
		value: "weekly",
		label: "Weekly"
		description: "",
		onSelect:
		checked:
		onMetaFieldChange: value;

	},
	fortnightly: {
		value: "fortnightly",
		label: "Fornightly"
		description: "",
		onSelect:
		checked:
		onMetaFieldChange: value;
	},
	monthly: {
		value: "monthly",
		label: "Monthly"
		description: "",
		onSelect: ,
		checked:,
		onMetaFieldChange: value;
	},
};



registerPlugin( 'hm-post-repeat', {
  icon: 'controls-repeat',
  render: () => {
    return (
      <>
        <PluginPostStatusInfo
          className="hm-post-repeat"
          title={__('Repeatable Post Options', 'hm-post-repeat')}
        >
          <span>Repeat:</span>
          <Dropdown
			position="bottom left"
			contentClassName="edit-post-hm-post-repeat__dialog"
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					aria-expanded={ isOpen }
					className="edit-post-hm-post-repeat__toggle"
					onClick={ onToggle }
					isLink
				>
					<HMPostRepeatFields />
					
				</Button>
			) }
			renderContent={ () => (
				<p>Some Content!</p>
			) };
		/>
        </PluginPostStatusInfo>
     </>
    )
  }
})

let HMPostRepeatFields = (props) => {
	return (
		<>
			{props.hmpostrepeat_metafield}
		</>
	)
}

// Grab stored meta value of hm-post-repeat using a redux store?!
HMPostRepeatFields = withSelect(
  (select) => {
    return {
      hmpostrepeat_metafield: select('core/editor').getEditedPostAttribute('meta')['hm-post-repeat']
    }
  }
)(HMPostRepeatFields);

// Save new meta value of hm-post-repeat using a redux store?!
HMPostRepeatFields = withDispatch(
  (dispatch) => {
    return {
      onMetaFieldChange: (value) => {
        dispatch('core/editor').editPost({meta: {'hm-post-repeat': value}})
      }
    }
  }
)(HMPostRepeatFields);


