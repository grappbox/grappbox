using Grappbox.Helpers;
using Grappbox.HttpRequest;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.IO;
using System.Linq;
using System.Runtime.InteropServices.WindowsRuntime;
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
    public sealed partial class AddUserToTaskDialog : ContentDialog
    {
        private LabelValue[] percentSource =
        {
            new LabelValue() {Label="0%", Value=0 },
            new LabelValue() {Label="10%", Value=10 },
            new LabelValue() {Label="20%", Value=20 },
            new LabelValue() {Label="30%", Value=30 },
            new LabelValue() {Label="40%", Value=40 },
            new LabelValue() {Label="50%", Value=50 },
            new LabelValue() {Label="60%", Value=60 },
            new LabelValue() {Label="70%", Value=70 },
            new LabelValue() {Label="80%", Value=80 },
            new LabelValue() {Label="90%", Value=90 },
            new LabelValue() {Label="100%", Value=100 }
        };
        private UserModel SelectedUser = null;
        public TaskUserModel NewResource = null;
        private bool UserSelected = false;
        private string ErrorMessage = "";

        public AddUserToTaskDialog(List<UserModel> users)
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            this.UserCombobox.ItemsSource = users;
        }

        public bool CheckData()
        {
            bool result = true;
            if (!UserSelected)
            {
                ErrorMessage = "Select a user";
                result = false;
            }
            else if (Combobox.SelectedItem == null)
            {
                ErrorMessage = "Select an occupation charge";
                result = false;
            }
            this.AlertTextBox.Text = ErrorMessage;
            if (result == false)
                AlertBox.Visibility = Visibility.Visible;
            else
                AlertBox.Visibility = Visibility.Collapsed;
            return result;
        }

        private async void SaveButton_Click(object sender, RoutedEventArgs e)
        {
            if (!CheckData())
                return;
            var percent = this.Combobox.SelectedItem as LabelValue;
            NewResource = new TaskUserModel()
            {
                Id = SelectedUser.Id,
                FirstName = SelectedUser.Firstname,
                LastName = SelectedUser.Lastname,
                Percent = percent.Value
            };
            this.Hide();
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            this.Hide();
        }

        private void UserCombobox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            UserSelected = true;
            SelectedUser = UserCombobox.SelectedItem as UserModel;
        }
    }
}