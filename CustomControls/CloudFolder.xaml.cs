using Grappbox.ViewModel;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class CloudFolder : ContentDialog
    {
        private CloudViewModel vm = CloudViewModel.GetViewModel();

        public CloudFolder()
        {
            this.InitializeComponent();
            this.DataContext = vm;
        }

        private void CancelFolder_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

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
