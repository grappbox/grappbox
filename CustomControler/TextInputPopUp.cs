using System;
using System.Collections.Generic;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;
using Windows.UI.Xaml.Data;
using Windows.UI.Xaml.Documents;
using Windows.UI.Xaml.Input;
using Windows.UI.Xaml.Media;

// Pour en savoir plus sur le modèle d'élément Contrôle basé sur un modèle, consultez la page http://go.microsoft.com/fwlink/?LinkId=234235

namespace GrappBox.CustomControler
{
    [TemplatePart(Name = ElementInputBox, Type = typeof(TextBox))]
    [TemplatePart(Name = ElementOkButton, Type = typeof(Button))]
    [TemplatePart(Name = ElementCancelButton, Type = typeof(Button))]
    public sealed class TextInputPopUp : Control
    {
        private const string ElementInputBox = "InputBox";
        private const string ElementOkButton = "OkButton";
        private const string ElementCancelButton = "Cancelbutton";

        private TextBox InputBox;
        private Button OkButton;
        private Button CancelButton;

        private static readonly DependencyProperty IsOpenProperty =
            DependencyProperty.Register("IsOpen", typeof(bool), typeof(TextInputPopUp), null );

        private bool result;

        public string ResultText
        {
            get { return InputBox.Text; }
        }

        public bool IsOpen
        {
            get { return (bool)GetValue(IsOpenProperty); }
            set { SetValue(IsOpenProperty, value); }
        }

        public TextInputPopUp()
        {
            this.DefaultStyleKey = typeof(TextInputPopUp);
        }
        protected override void OnApplyTemplate()
        {
            base.OnApplyTemplate();
            InputBox = GetTemplateChild(ElementInputBox) as TextBox;
            OkButton = GetTemplateChild(ElementOkButton) as Button;
            CancelButton = GetTemplateChild(ElementCancelButton) as Button;

            InputBox.TextChanged += InputBox_TextChanged;
            OkButton.Click += OkButton_Click;
            CancelButton.Click += CancelButton_Click;
        }

        public bool ShowDialog()
        {
            InputBox.Text = "";
            IsOpen = true;
            while (IsOpen == true) ;
            return result;
        }

        private void InputBox_TextChanged(object sender, TextChangedEventArgs e)
        {
            if (InputBox.Text.Length != 0)
                OkButton.IsEnabled = true;
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            IsOpen = false;
        }

        private void OkButton_Click(object sender, RoutedEventArgs e)
        {

            IsOpen = false;
        }
    }
}