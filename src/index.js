import { registerPlugin } from "@wordpress/plugins";
import { PluginPostStatusInfo } from "@wordpress/edit-post";
import { Button, Dropdown } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from "@wordpress/i18n";


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
				<fieldset
					key="hm-post-repeat-selector"
					className="hm-post-repeat__dialog-fieldset"
				>
					<legend className="hm-post-repeat__dialog-legend">
						{ __( 'Repeat Frequency' ) }
					</legend>
					
					<div className="hm-post-repeat__choice">
						<input
							type="radio"
							name={ `hm-post-repeat__setting-no` }
							value=""
							className="hm-post-repeat__dialog-radio"
						/>
						<label
							htmlFor={ `hm-post-repeat-no-` }
							className="hm-post-repeat__dialog-label"
						>
							No
						</label>
						<p>
								No repeat selected
						</p>
					</div>
					<div className="hm-post-repeat__choice">
						<input
							type="radio"
							name={ `hm-post-repeat__setting-` }
							value=""
							className="hm-post-repeat__dialog-radio"
						/>
						<label
							htmlFor={ `hm-post-repeat-no-` }
							className="hm-post-repeat__dialog-label"
						>
							Daily
						</label>
						<p>
								Information description
						</p>
					</div>
					<div className="hm-post-repeat__choice">
						<input
							type="radio"
							name={ `hm-post-repeat__setting-` }
							value=""
							className="hm-post-repeat__dialog-radio"
						/>
						<label
							htmlFor={ `hm-post-repeat-no-` }
							className="hm-post-repeat__dialog-label"
						>
							Weekly
						</label>
						<p>
								Information description
						</p>
					</div>
					<div className="hm-post-repeat__choice">
						<input
							type="radio"
							name={ `hm-post-repeat__setting-` }
							value=""
							className="hm-post-repeat__dialog-radio"
						/>
						<label
							htmlFor={ `hm-post-repeat-no-` }
							className="hm-post-repeat__dialog-label"
						>
							Fortnightly
						</label>
						<p>
								Every 2 weeks
						</p>
					</div>
					<div className="hm-post-repeat__choice">
						<input
							type="radio"
							name={ `hm-post-repeat__setting-` }
							value=""
							className="hm-post-repeat__dialog-radio"
						/>
						<label
							htmlFor={ `hm-post-repeat-no-` }
							className="hm-post-repeat__dialog-label"
						>
							Monthly
						</label>
						<p>
								Information description
						</p>
					</div>
					
				</fieldset>
			) }
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
HMPostRepeatFields: withSelect(
  (select => {
    return {
      hmpostrepeat_metafield: select('core/editor').getEditedPostAttribute('meta')['hm-post-repeat']
    };
  })(({ hmpostrepeat_metafield, value, label, description }) => {
	    if (!hmpostrepeat_metafield) {
	        return (
				value= "",
				label= "No",
				description= "No repeating of post."
	    	)
	    }
	    if ('fortnightly' === hmpostrepeat_metafield) {
	        return (
				value= "",
				label= "Fornightly JW",
				description= "No repeating of post."
	    	)
	    }
	    return (
	    	<>something</>
	    );
	}) 
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


