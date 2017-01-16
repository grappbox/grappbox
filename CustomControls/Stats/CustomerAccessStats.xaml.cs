using Windows.UI.Xaml.Controls;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace Grappbox.CustomControls.Stats
{
    /// <summary>
    /// Customer Access stats view
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class CustomerAccessStats : UserControl
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="CustomerAccessStats"/> class.
        /// </summary>
        public CustomerAccessStats()
        {
            this.InitializeComponent();
        }
    }
}
