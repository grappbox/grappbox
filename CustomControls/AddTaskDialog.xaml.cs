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
    public sealed partial class AddTaskDialog : ContentDialog
    {
        public TaskModel NewTask = null;
        private string ErrorMessage = "";

        public AddTaskDialog(List<TaskModel> tasks)
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            this.TaskCombobox.ItemsSource = tasks;
        }

        public bool CheckData()
        {
            bool result = true;
            if (NewTask == null)
            {
                ErrorMessage = "Select a task";
                result = false;
            }
            this.AlertTextBox.Text = ErrorMessage;
            if (result == false)
                AlertBox.Visibility = Visibility.Visible;
            else
                AlertBox.Visibility = Visibility.Collapsed;
            return result;
        }

        private void SaveButton_Click(object sender, RoutedEventArgs e)
        {
            if (!CheckData())
                return;
            this.Hide();
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            this.Hide();
        }

        private void TaskCombobox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            NewTask = TaskCombobox.SelectedItem as TaskModel;
        }
    }
}