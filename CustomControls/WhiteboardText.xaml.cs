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

// Pour plus d'informations sur le modèle d'élément Boîte de dialogue de contenu, voir la page http://go.microsoft.com/fwlink/?LinkId=234238

namespace Grappbox.CustomControls
{
    public sealed partial class WhiteboardText : ContentDialog
    {
        public WhiteboardText()
        {
            IsTextConfirmed = false;
            this.InitializeComponent();
        }
        public static readonly DependencyProperty TextConfirmedProperty =
            DependencyProperty.Register("IsTextConfirmed", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty TextEnteredProperty =
            DependencyProperty.Register("TextEntered", typeof(string), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty IsBoldProperty =
            DependencyProperty.Register("IsBold", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty IsItalicProperty =
            DependencyProperty.Register("IsItalic", typeof(bool), typeof(WhiteBoardText), null);
        public static readonly DependencyProperty TextFontSizeProperty =
            DependencyProperty.Register("FontSizeProp", typeof(int), typeof(WhiteBoardText), null);
        public int FontSizeProp
        {
            get { return (int)GetValue(TextFontSizeProperty); }
            set { SetValue(TextFontSizeProperty, value); }
        }
        public bool IsItalic
        {
            get { return (bool)GetValue(IsItalicProperty); }
            set { SetValue(IsItalicProperty, value); }
        }
        public bool IsBold
        {
            get { return (bool)GetValue(IsBoldProperty); }
            set { SetValue(IsBoldProperty, value); }
        }
        public bool IsTextConfirmed
        {
            get { return (bool)GetValue(TextConfirmedProperty); }
            set { SetValue(TextConfirmedProperty, value); }
        }
        public string TextEntered
        {
            get { return (string)GetValue(TextEnteredProperty); }
            set { SetValue(TextEnteredProperty, value); }
        }
        private void ConfirmText_Click(object sender, RoutedEventArgs e)
        {
            TextEntered = textBlock.Text;
            textBlock.Text = "";
            IsTextConfirmed = true;
            IsTextConfirmed = false;
            BoldCheckBox.IsChecked = false;
            ItalicCheckBox.IsChecked = false;
        }
        private void CancelText_Click(object sender, RoutedEventArgs e)
        {
            IsTextConfirmed = false;
            BoldCheckBox.IsChecked = false;
            ItalicCheckBox.IsChecked = false;
            textBlock.Text = "";
            this.Hide();
        }
        private void textBox_TextChanged(object sender, TextChangedEventArgs e)
        {
            if (textBlock.Text.Length == 0)
                ConfirmText.IsEnabled = false;
            else
                ConfirmText.IsEnabled = true;
        }
    }
}
}
