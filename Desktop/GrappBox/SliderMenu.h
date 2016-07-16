#ifndef SLIDERMENU_H
#define SLIDERMENU_H

#include <QMap>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QPushButton>
#include <QtWidgets/QWidget>
#include <QtWidgets/QScrollArea>
#include <QtWidgets/QComboBox>
#include <QtWidgets/QLabel>

#include <QByteArray>
#include <QVector>

class SliderMenu : public QWidget
{
    Q_OBJECT
public:
    explicit SliderMenu(QWidget *parent = 0);
    void AddMenuItem(QString name, int id, bool hided = false, bool needProject = true);
    QString GetMenuItem(int id);
    void UpdateProject();

signals:
    void MenuChanged(int);
    void ProjectChange();

public slots:
    void ForceChangeMenu(int menu);
    void ButtonChangeMenu();
    void ProjectLoaded(int id, QByteArray data);
    void ProjectFailLoad(int id, QByteArray data);
    void OnProjectChange(int id);

private:
    void UpdateButtonDisable();

private:
    QVBoxLayout         *_MainLayout;

private:
    QMap<int, QPushButton*>     _ListButton;
    QMap<int, bool>             _NeedProjectButton;
    QLabel                      *_LabelComboBox;
    QComboBox                   *_ComboBoxProject;
    int                         _CurrentIndex;
};

#endif // SLIDERMENU_H
