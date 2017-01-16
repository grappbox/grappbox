using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.Foundation.Collections;
using Windows.UI;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Controls.Primitives;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Navigation;
using Windows.Web.Http;

namespace Grappbox.CustomControls
{
    public sealed partial class SelectTagDialog : ContentDialog
    {
        public TagModel SelectedTag;
        public string ErrorMessage = "";

        public SelectTagDialog(ObservableCollection<TagModel> tagList)
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            TagGridView.ItemsSource = tagList;
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            SelectedTag = null;
            this.Hide();
        }

        private void TagGridView_ItemClick(object sender, ItemClickEventArgs e)
        {
            SelectedTag = e.ClickedItem as TagModel;
            this.Hide();
        }
    }
}