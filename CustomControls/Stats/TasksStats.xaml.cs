using Windows.UI.Xaml.Controls;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace Grappbox.CustomControls.Stats
{
    /// <summary>
    /// Tasks stats view
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class TasksStats : UserControl
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="TasksStats"/> class.
        /// </summary>
        public TasksStats()
        {
            this.InitializeComponent();
        }
    }
}
