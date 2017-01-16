using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Cloud password content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.ContentDialog" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class CloudPassword : ContentDialog
    {
        private CloudViewModel vm = CloudViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="CloudPassword"/> class.
        /// </summary>
        public CloudPassword()
        {
            this.InitializeComponent();
            this.DataContext = vm;
        }

        /// <summary>
        /// Handles the Click event of the CancelPassword control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void CancelPassword_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the PostPassword control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void PostPassword_Click(object sender, RoutedEventArgs e)
        {
            if (!string.IsNullOrEmpty(Password.Password))
            {
                vm.Password = Password.Password;
                dialog.Hide();
            }
        }
    }
}
