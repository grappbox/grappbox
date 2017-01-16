using Grappbox.Model;
using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Bugtracker Tag Content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.ContentDialog" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class BugTagContentDialog : ContentDialog
    {
        private BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="BugTagContentDialog"/> class.
        /// </summary>
        /// <param name="model">The model.</param>
        public BugTagContentDialog(TagModel model)
        {
            this.InitializeComponent();
            this.DataContext = model;
            vm.TagSelect = model;
            if (model == null)
                Trash.Visibility = Visibility.Collapsed;
            else
                Trash.Visibility = Visibility.Visible;
        }

        /// <summary>
        /// Handles the Click event of the CancelBugTag control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void CancelBugTag_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the PostBugTag control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void PostBugTag_Click(object sender, RoutedEventArgs e)
        {
            string color = TagColor.Text.Replace("#", string.Empty);
            if (TagName.Text != "" && System.Text.RegularExpressions.Regex.IsMatch(color, @"\A\b[0-9a-fA-F]+\b\Z"))
            {
                if (vm.TagSelect != null)
                {
                    if (await vm.editTag())
                        dialog.Hide();
                }
                else
                {
                    if (await vm.addTag(TagName.Text, color))
                        dialog.Hide();
                }
            }
        }

        /// <summary>
        /// Handles the Click event of the DeleteBugTag control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void DeleteBugTag_Click(object sender, RoutedEventArgs e)
        {
            if (await vm.deleteTag())
                dialog.Hide();
        }
    }
}
