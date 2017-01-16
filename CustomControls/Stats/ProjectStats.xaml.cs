using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using WinRTXamlToolkit.Controls.DataVisualization.Charting;

// The User Control item template is documented at http://go.microsoft.com/fwlink/?LinkId=234236

namespace Grappbox.CustomControls.Stats
{
    /// <summary>
    /// Project stats view
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.UserControl" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class ProjectStats : UserControl
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="ProjectStats"/> class.
        /// </summary>
        public ProjectStats()
        {
            this.InitializeComponent();
        }

        /// <summary>
        /// Handles the Loaded event of the ScrollViewer control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void ScrollViewer_Loaded(object sender, RoutedEventArgs e)
        {
            ((LineSeries)ProjectAdvancement.Series[0]).DependentRangeAxis =
                        new LinearAxis
                        {
                            Minimum = 0,
                            Maximum = 100,
                            Orientation = AxisOrientation.Y,
                            Interval = 10,
                            ShowGridLines = true
                        };
        }
    }
}
