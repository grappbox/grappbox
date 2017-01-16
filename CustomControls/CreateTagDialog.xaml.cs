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
    public sealed partial class CreateTagDialog : ContentDialog
    {
        public TagModel postTagModel;
        public string ErrorMessage = "";
        public static List<SolidColorBrush> colors = null;
        static CreateTagDialog()
        {
            if (colors == null)
            {
                colors = new List<SolidColorBrush>();
                for (int i = 1; i < 24; ++i)
                {
                    object o;
                    if (Application.Current.Resources.TryGetValue("Color_" + i + "Brush", out o) == true)
                    {
                        SolidColorBrush scb = o as SolidColorBrush;
                        colors.Add(scb);
                    }
                }
            }
        }

        public string ColorToHexa(Color color)
        {
            string hex = "#";
            hex += color.R.ToString("X2");
            hex += color.G.ToString("X2");
            hex += color.B.ToString("X2");
            return hex;
        }

        public DependencyProperty SelectedColorProperty = DependencyProperty
            .Register("SelectedColor", typeof(SolidColorBrush), typeof(ColorDialog), null);
        public SolidColorBrush SelectedColor
        {
            get { return (SolidColorBrush)GetValue(SelectedColorProperty); }
            set { SetValue(SelectedColorProperty, value); }
        }
        public CreateTagDialog()
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            GridViewColors.ItemsSource = colors;
        }

        public bool CheckData()
        {
            bool result = true;
            if (string.IsNullOrWhiteSpace(TagNameTextBox.Text))
            {
                ErrorMessage = "Name is required";
                result = false;
            }
            else if (TagNameTextBox.Text.Length > 20)
            {
                ErrorMessage = "Name is too long";
                result = false;
            }
            else if (string.IsNullOrWhiteSpace(ColorTextBox.Text))
            {
                ErrorMessage = "Tag must have a color";
                result = false;
            }
            else if (!Regex.Match(ColorTextBox.Text, "^#[A-Fa-f0-9]{6}$").Success)
            {
                ErrorMessage = "Not a valid color";
                result = false;
            }
            this.AlertTextBox.Text = ErrorMessage;
            if (result == false)
                AlertBox.Visibility = Visibility.Visible;
            else
                AlertBox.Visibility = Visibility.Collapsed;
            return result;
        }

        private void GridViewColors_ItemClick(object sender, ItemClickEventArgs e)
        {
            var brush = e.ClickedItem as SolidColorBrush;
            string hex = ColorToHexa(brush.Color);
            ColorTextBox.Text = hex;
        }


        private void SaveButton_Click(object sender, RoutedEventArgs e)
        {
            if (!CheckData())
                return;
            postTagModel = new TagModel()
            {
                Id = 0,
                Color = this.ColorTextBox.Text.Substring(1, ColorTextBox.Text.Length-1),
                Name = this.TagNameTextBox.Text
            };
            this.Hide();
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            postTagModel = null;
            this.Hide();
        }
    }
}