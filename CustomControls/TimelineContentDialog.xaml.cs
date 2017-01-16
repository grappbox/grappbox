using Grappbox.ViewModel;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

// The Content Dialog item template is documented at http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    /// <summary>
    /// Timeline content dialog
    /// </summary>
    /// <seealso cref="Windows.UI.Xaml.Controls.ContentDialog" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector" />
    /// <seealso cref="Windows.UI.Xaml.Markup.IComponentConnector2" />
    public sealed partial class TimelineContentDialog : ContentDialog
    {
        public static readonly DependencyProperty IdProperty = DependencyProperty.Register("Id", typeof(string), typeof(TimelineContentDialog), null);

        public int? Id
        {
            get { return GetValue(IdProperty) as int?; }
            set { SetValue(IdProperty, value); }
        }

        /// <summary>
        /// Initializes a new instance of the <see cref="TimelineContentDialog"/> class.
        /// </summary>
        /// <param name="id">The identifier.</param>
        public TimelineContentDialog(int id)
        {
            this.InitializeComponent();
            Id = id;
        }

        /// <summary>
        /// Handles the Click event of the CancelCustomer control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
        private void CancelCustomer_Click(object sender, RoutedEventArgs e)
        {
            dialog.Hide();
        }

        /// <summary>
        /// Handles the Click event of the PostCustomerMessage control.
        /// </summary>
        /// <param name="sender">The source of the event.</param>
        /// <param name="e">The <see cref="RoutedEventArgs"/> instance containing the event data.</param>
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
