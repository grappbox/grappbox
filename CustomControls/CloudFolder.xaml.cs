using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Cloud folder content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.ContentDialog" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class CloudFolder : ContentDialog
    {
        private CloudViewModel vm = CloudViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="CloudFolder"/> class.
        /// </summary>
        public CloudFolder()
        {
            this.InitializeComponent();
            this.DataContext = vm;
        }

        /// <summary>
        /// Handles the Click event of the CancelFolder control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void CancelFolder_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the PostFolder control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private async void PostFolder_Click(object sender, RoutedEventArgs e)
        {
            if (!string.IsNullOrEmpty(FolderName.Text))
            {
                vm.FolderName = FolderName.Text;
                if (await vm.createDir())
                    dialog.Hide();
            }
        }
    }
}
