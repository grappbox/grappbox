using Grappbox.Model;
using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class BugTagContentDialog : ContentDialog
    {
        public static readonly DependencyProperty TagModelProperty = DependencyProperty.Register("TagModel", typeof(string), typeof(BugTagContentDialog), null);
        private BugtrackerViewModel vm = BugtrackerViewModel.GetViewModel();
        
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
        
        private void CancelBugTag_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

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

        private async void DeleteBugTag_Click(object sender, RoutedEventArgs e)
        {
            if (await vm.deleteTag())
                dialog.Hide();
        }
    }
}
