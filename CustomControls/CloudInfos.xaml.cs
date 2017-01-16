using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Cloud info content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.ContentDialog" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class CloudInfos : ContentDialog
    {
        private CloudViewModel vm = CloudViewModel.GetViewModel();

        /// <summary>
        /// Initializes a new instance of the <see cref="CloudInfos"/> class.
        /// </summary>
        public CloudInfos()
        {
            this.InitializeComponent();
        }

        /// <summary>
        /// Gets the filename.
        /// </summary>
        /// <value>
        /// The filename.
        /// </value>
        public string Filename
        {
            get
            {
                return vm.FileSelect.Filename;
            }
        }

        /// <summary>
        /// Gets the size of the file.
        /// </summary>
        /// <value>
        /// The size of the file.
        /// </value>
        public string FileSize
        {
            get
            {
                return vm.FileSelect.FileSize;
            }
        }

        /// <summary>
        /// Gets the mimetype.
        /// </summary>
        /// <value>
        /// The mimetype.
        /// </value>
        public string Mimetype
        {
            get
            {
                return vm.FileSelect.Mimetype;
            }
        }

        /// <summary>
        /// Gets the infos.
        /// </summary>
        /// <value>
        /// The infos.
        /// </value>
        public string Infos
        {
            get
            {
                return vm.FileSelect.Infos;
            }
        }

        /// <summary>
        /// Handles the Click event of the CancelInfos control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void CancelInfos_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }
    }
}
