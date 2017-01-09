using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class CloudInfos : ContentDialog
    {
        private CloudViewModel vm = CloudViewModel.GetViewModel();

        public CloudInfos()
        {
            this.InitializeComponent();
        }

        public string Filename
        {
            get
            {
                return vm.FileSelect.Filename;
            }
        }

        public string FileSize
        {
            get
            {
                return vm.FileSelect.FileSize;
            }
        }

        public string Mimetype
        {
            get
            {
                return vm.FileSelect.Mimetype;
            }
        }

        public string Infos
        {
            get
            {
                return vm.FileSelect.Infos;
            }
        }

        private void CancelInfos_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }
    }
}
