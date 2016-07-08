using GrappBox.ApiCom;
using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Diagnostics;
using System.Linq;
using Windows.Web.Http;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Popups;

namespace GrappBox.ViewModel
{
    class GenericDashboardViewModel : ViewModelBase
    {
        private ObservableCollection<ProjectListModel> _projectList;
        public ObservableCollection<ProjectListModel> ProjectList
        {
            get { return _projectList; }
            set { _projectList = value; NotifyPropertyChanged("ProjectList"); }
        }

        public async System.Threading.Tasks.Task getProjectList()
        {
            ApiCommunication api = ApiCommunication.GetInstance();
            object[] token = { User.GetUser().Token };
            HttpResponseMessage res = await api.Get(token, "dashboard/getprojectsglobalprogress");
            if (res.IsSuccessStatusCode)
            {
                Debug.WriteLine(await res.Content.ReadAsStringAsync());
                ProjectList = api.DeserializeArrayJson<ObservableCollection<ProjectListModel>>(await res.Content.ReadAsStringAsync());
            }
            else
            {
                MessageDialog msgbox = new MessageDialog(api.GetErrorMessage(await res.Content.ReadAsStringAsync()));
                await msgbox.ShowAsync();
            }
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
