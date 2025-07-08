import os
import zipfile
import requests
import hashlib
import re
from io import BytesIO

class UpdateTP:
    def __init__(self, version="8.1.2", tp_dir="../thinkphp"):
        self.version = version
        self.tp_dir = tp_dir
        self.download_url = f"https://github.com/top-think/framework/archive/v{self.version}.zip"

    def check_thinkphp_version(self):
        """检查当前的 ThinkPHP 版本"""
        version_file = os.path.join(self.tp_dir, "base.php")
        if os.path.exists(version_file):
            with open(version_file, 'r', encoding='utf-8') as f:  # 显式指定编码为 utf-8
                contents = f.read()

                # 调试输出文件的前500个字符，以确保文件读取正确
                print(contents[:500])  # 打印前500个字符，用于调试

                # 使用正则表达式匹配版本号
                match = re.search(r"define\('THINK_VERSION',\s*'([0-9\.]+)'\);", contents)
                if match:
                    version = match.group(1)  # 提取版本号
                    return version
                else:
                    raise Exception("未找到版本定义：define('THINK_VERSION', ...)")

        raise Exception(f"未找到文件 {version_file} 或文件格式不正确")

    def get_file(self, url, path='', filename='', type=0):
        """下载文件"""
        if url == '':
            return False
        # 使用 requests 下载文件
        response = requests.get(url)
        if response.status_code != 200:
            raise Exception("下载错误，无法获取文件！")
        
        img = response.content

        if not filename:
            filename = hashlib.md5(img).hexdigest()

        # 获取文件扩展名
        ext = url.split('.')[-1]
        if ext and len(ext) < 5:
            filename += f".{ext}"

        # 确保路径存在
        if not os.path.exists(path):
            os.makedirs(path)

        file_path = os.path.join(path, filename)
        with open(file_path, 'wb') as f:
            f.write(img)
        return file_path

    def unzip(self, zip_path):
        """解压文件"""
        if not zipfile.is_zipfile(zip_path):
            raise Exception(f"{zip_path} 不是有效的 ZIP 文件")
        
        with zipfile.ZipFile(zip_path, 'r') as zip_ref:
            zip_ref.extractall(self.tp_dir)

        return self.tp_dir

    def download(self, type=0):
        """下载目标版本的 ThinkPHP 框架"""
        current_version = self.check_thinkphp_version()
        if current_version and current_version >= self.version:
            raise Exception("当前版本不需要更新！")
        
        # 下载指定版本的文件
        file_path = self.get_file(self.download_url, '', f"{self.version}.zip", type)
        return file_path

    def start(self, type=2):
        """开始更新"""
        zip_file = f"{self.version}.zip"
        if not os.path.exists(zip_file):
            zip_file = self.download(type)
        
        # 解压下载的 ZIP 文件
        extracted_dir = self.unzip(zip_file)
        return extracted_dir


if __name__ == "__main__":
    try:
        updater = UpdateTP(version="8.1.2")  # 设置目标版本为 8.1.2
        extracted_dir = updater.start()
        print(f"成功解压到: {extracted_dir}")
    except Exception as e:
        print(f"发生错误: {e}")
