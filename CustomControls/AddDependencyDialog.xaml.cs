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
    public sealed partial class AddDependencyDialog : ContentDialog
    {
        public LabelString[] dependencies =
        {
            new LabelString{ Label="Finish to Start", Value="fs"},
            new LabelString{ Label="Start to Finish", Value="sf"},
            new LabelString{ Label="Start to Start", Value="ss"},
            new LabelString{ Label="Finish to Finish", Value="ff"}
        };
        string relation = "";
        private TaskModel SelectedTask = null;
        public DependencyTask NewTask = null;
        private string ErrorMessage = "";

        public AddDependencyDialog(List<TaskModel> tasks)
        {
            this.FullSizeDesired = true;
            this.InitializeComponent();
            this.TaskCombobox.ItemsSource = tasks;
            this.RelationCombobox.ItemsSource = dependencies;
        }

        public bool CheckData()
        {
            bool result = true;
            if (SelectedTask == null)
            {
                ErrorMessage = "Select a task";
                result = false;
            }
            if (String.IsNullOrEmpty(relation))
            {
                ErrorMessage = "Select a dependency";
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
            NewTask = new DependencyTask()
            {
                Name = relation,
                Id = SelectedTask.Id,
                Task = SelectedTask
            };
            this.Hide();
        }

        private void CancelButton_Click(object sender, RoutedEventArgs e)
        {
            this.Hide();
        }

        private void TaskCombobox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            SelectedTask = TaskCombobox.SelectedItem as TaskModel;
        }

        private void RelationCombobox_SelectionChanged(object sender, SelectionChangedEventArgs e)
        {
            relation = RelationCombobox.SelectedValue as string;
        }
    }
}