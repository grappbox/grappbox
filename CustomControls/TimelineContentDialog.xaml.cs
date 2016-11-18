using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class TimelineContentDialog : ContentDialog
    {
        public static readonly DependencyProperty IdProperty = DependencyProperty.Register("Id", typeof(string), typeof(TimelineContentDialog), null);

        public int? Id
        {
            get { return GetValue(IdProperty) as int?; }
            set { SetValue(IdProperty, value); }
        }

        public TimelineContentDialog(int id)
        {
            this.InitializeComponent();
            Id = id;
        }

        private void CancelCustomer_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

        private async void PostCustomerMessage_Click(object sender, RoutedEventArgs e)
        {
            if (MessageTitles.Text != "" && Messages.Text != "")
            {
                await TimelineViewModel.GetViewModel().postMessage((int)this.Id, MessageTitles.Text, Messages.Text);
            }
            dialog.Hide();
        }
    }
}
