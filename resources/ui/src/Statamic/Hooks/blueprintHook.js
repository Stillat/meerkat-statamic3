import commentIcon from '../../Icons/comment.html';
import ControlPanel from '../controlPanel';

class BlueprintHook {

  static run(app) {
    app.controlPanel.blueprints().addOtherEntry(
      commentIcon,
      'Meerkat Comments',
      ControlPanel.cpUrl('meerkat/blueprint')
    );
  }

}

export default BlueprintHook;
