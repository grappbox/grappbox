using GrappBox.HttpRequest;
using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;
using Windows.Web.Http;

namespace GrappBox.ViewModel
{
    internal class GenericDashboardViewModel : ViewModelBase
    {
        private ObservableCollection<ProjectListModel> _projectList;

        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }

        public async Task<bool> getProjectList()
        {
            HttpRequestManager api = HttpRequestManager.Instance;
            HttpResponseMessage res = await api.Get(null, Constants.DashboardGenericCall);
            if (res == null)
                return false;
            string response = await res.Content.ReadAsStringAsync();
            Debug.WriteLine("response= " + response);
            if (res.IsSuccessStatusCode)
            {
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(response);
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(response));
                await msgbox.ShowAsync();
                return false;
            }
            return true;
        }

        public async System.Threading.Tasks.Task getProjectsLogo()
        {
            foreach (ProjectListModel plm in ProjectList)
            {
                await plm.LogoUpdate();
                await plm.SetLogo();
            }
        }
    }
}