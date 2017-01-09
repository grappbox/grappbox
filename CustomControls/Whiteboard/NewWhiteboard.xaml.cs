using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

namespace Grappbox.CustomControls.Whiteboard
{
    public sealed partial class NewWhiteboard : ContentDialog
    {
        public ContentDialogResult Result;
        public static readonly DependencyProperty WhiteBoardNameProperty = DependencyProperty.Register(
            "WhiteBoardName", typeof(string), typeof(NewWhiteboard), new PropertyMetadata(default(string)));
        public string WhiteBoardName
        {
            get { return (string)GetValue(WhiteBoardNameProperty); }
            set { SetValue(WhiteBoardNameProperty, value); }
        }
        public NewWhiteboard()
        {
            this.InitializeComponent();
            Result = ContentDialogResult.None;
        }

        private void TextBox_TextChanged(object sender, TextChangedEventArgs e)
        {
            TextBox textBox = sender as TextBox;
            if (string.IsNullOrWhiteSpace(textBox.Text) == true)
                CreateButton.IsEnabled = false;
            else
                CreateButton.IsEnabled = true;
        }

        private void CreateButton_Click(object sender, RoutedEventArgs e)
        {
            Result = ContentDialogResult.Primary;
            this.Hide();
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            Result = ContentDialogResult.Secondary;
            this.Hide();
        }
    }
}
